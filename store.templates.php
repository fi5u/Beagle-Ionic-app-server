<?php
$fetch_collection = 'templates';
include_once 'store.connect.php';

/* Set up the return array */
$success = array();
$success['msg'] = array();

/* save, remove, update */
$type = $obj_data->type;

/* Once $type is set, place data on obj_data var */
$obj_data = $obj_data->data;

if ($type !== 'get') {
    $criteria_new = null;
    if ($type !== 'update') {
        /* Find if the template already exists in the collection */
        $template_query = $collection->findOne(array('template' => $obj_data->template));

        /* Set hashed keys - mongo cannot store a key with . or $ */
        set_hashed_key('url', $obj_data);
        set_hashed_key('title', $obj_data);

        /* Set DB criteria */
        $criteria = array('template' => $obj_data->template);
    } else { // is update
        /* Find if the old and new templates already exists in the collection */
        $template_query = $collection->findOne(array('template' => $obj_data->tmpl_old->template));
        $obj_data_new = $obj_data->tmpl_new;
        $obj_data_old = $obj_data->tmpl_old;

        /* Set hashed keys - mongo cannot store a key with . or $ */
        $obj_data_old = set_hashed_key('url', $obj_data_old);
        $obj_data_old = set_hashed_key('title', $obj_data_old);
        $obj_data_new = set_hashed_key('url', $obj_data_new);
        $obj_data_new = set_hashed_key('title', $obj_data_new);

        /* Set DB criteria */
        $criteria = array('template' => $obj_data_old->template);
        $criteria_new = array('template' => $obj_data_new->template);
    }

    $template_exists = false;
    if (count($template_query) > 0) {
        $template_exists = true;
    }

} else {
    $hashed_url = get_hashed_key($obj_data->url);
    $query = 'url.' . $hashed_url;
    $template_query = $collection->findOne(array($query => array('$exists' => true)));
}

$tries = 0;
$caught = true;
while ($tries < 3 && $caught) {
    try {
        $caught = false;

        switch ($type) {
            case 'get':
                if (count($template_query) > 0) {
                    /* Get title with highest frequency */
                    $highest_title = array();
                    foreach ($template_query['title'] as $key => $value) {
                        if (!empty($highest_title)) {
                            if ($value['freq'] >= $highest_title[1]) {
                                $highest_title[0] = $value['value'];
                                $highest_title[1] = $value['freq'];
                            }
                        } else {
                            $highest_title[0] = $value['value'];
                            $highest_title[1] = $value['freq'];
                        }
                    }

                    /* Get space symbol with highest frequency */
                    $highest_space = array();
                    foreach ($template_query['space'] as $key => $value) {
                        if (!empty($highest_space)) {
                            if ($value >= $highest_space[1]) {
                                $highest_space[0] = $key;
                                $highest_space[1] = $value;
                            }
                        } else {
                            $highest_space[0] = $key;
                            $highest_space[1] = $value;
                        }
                    }

                    $return_arr = array(
                        'template' => $template_query['template'],
                        'title' => $highest_title[0],
                        'space' => $highest_space[0]
                    );
                    set_success(true, $obj_data->url . ' exists', '', $return_arr);
                } else {
                    set_success(false, '', $obj_data->url . ' does not exist');
                }
                break;
            case 'save':
                save($template_query, $obj_data, $template_exists);
                break;

            case 'remove':
                remove($template_query, $obj_data, $template_exists);
                break;

            case 'update':
                /* Remove old template */
                remove($template_query, $obj_data_old, $template_exists);

                $template_query_new = $collection->findOne(array('template' => $obj_data->tmpl_new->template));
                $template_new_exists = false;
                if (isset($template_query_new) && count($template_query_new) > 0) {
                    $template_new_exists = true;
                }

                /* Save new template */
                save($template_query_new, $obj_data_new, $template_new_exists, true);

                break;
        }
    } catch (MongoConnectionException $e) {
        $success['type'] = false;
        $success['msg'][] = 'Error connecting to MongoDB server';
        $caught = true;
    } catch (MongoException $e) {
        $success['type'] = false;
        $success['msg'][] = 'Mongo Error: ' . $e->getMessage();
        $caught = true;
    } catch (Exception $e) {
        $success['type'] = false;
        $success['msg'][] = 'Error: ' . $e->getMessage();
        $caught = true;
    }
}

echo json_encode($success);


/* FUNCTIONS */

/**
 * Set the success message
 * @param bool $test_var   Whether successful or unsuccessful
 * @param str  $text_true  Text to display if successful
 * @param str  $text_false Text to display if successful
 */
function set_success($test_var, $text_true, $text_false, $data = null) {
    global $success;
    if ($test_var) {
        $success['type'] = true;
        $success['msg'][] = $text_true;
    } else {
        $success['type'] = false;
        $success['msg'][] = $text_false;
    }
    if ($data) {
        $success['data'] = $data;
    }
}

/**
 * Store the data to the db
 * @param str  $key            Key to store
 * @param arr  $template_query Item stored on db
 * @param str  $msg_pos        Message if successful
 * @param str  $msg_neg        Message if unsuccessful
 * @param bool $is_updated     If this is an update
 */
function set_data($key, $template_query, $msg_pos, $msg_neg, $is_updated) {
    global $collection;
    global $criteria;
    global $criteria_new;

    $set_arr[$key] = $template_query[$key];
    $new_data = array('$set' => $set_arr);
    if ($is_updated) {
        $updated = $collection->update($criteria_new, $new_data);
    } else {
        $updated = $collection->update($criteria, $new_data);
    }
    set_success($updated, $msg_pos, $msg_neg);
}

/**
 * Set the frequency of the template
 * @param bool $up             Whether to increase
 * @param arr  $template_query Item stored on db
 * @param bool $is_updated     If this is an update
 */
function set_template_freq($up, $template_query, $is_updated) {
    global $collection;

    if ($up) {
        /* Increment template frequency */
        ++$template_query['freq'];
    } else {
        if ($template_query['freq'] > 1) {
            /* Decrement template frequency */
            --$template_query['freq'];
        }
    }

    set_data('freq', $template_query, 'template frequency changed', 'template frequency could not be changed', $is_updated);
}

/**
 * Set an array object which is constucted like: key=>array(subkey1=>1, subkey2=>0)
 * @param str  $key            Key name
 * @param str  $subkey1        Subkey1 name
 * @param str  $subkey2        Subkey2 name
 * @param bool $up             Whether to increase
 * @param arr  $template_query Item stored on the db
 * @param arr  $obj_data       Original object data
 * @param bool $is_updated     If this is an update
 */
function set_key_1_0($key, $subkey1, $subkey2, $up, $template_query, $obj_data, $is_updated) {
    $feedback = array(); // Store the feedback [subkey, method]

    if (isset($template_query[$key])) {
        if ($up) {
            if ($obj_data->{$key}->{$subkey1} === 1) {
                ++$template_query[$key][$subkey1];
                $feedback = [$subkey1, 'incremented'];
            } else {
                ++$template_query[$key][$subkey2];
                $feedback = [$subkey2, 'incremented'];
            }
        } else {
            if ($obj_data->{$key}->{$subkey1} === 1) {
                if ($template_query[$key][$subkey1] > 0) {
                    --$template_query[$key][$subkey1];
                    $feedback = [$subkey1, 'decremented'];
                } else { return false; }
            } else {
                if ($template_query[$key][$subkey2] > 0) {
                    --$template_query[$key][$subkey2];
                    $feedback = [$subkey2, 'decremented'];
                } else { return false; }
            }
        }
    } else { return false; }
    set_data($key, $template_query, $key . ' ' . $feedback[0] . ' ' . $feedback[1], $key . ' ' . $feedback[0] . ' could not be ' . $feedback[1], $is_updated);
}

/**
 * For saving data like
 * "space": {"+": 2, "-": 1}
 * when there is only one subkey (i.e. 'space')
 *
 * @param str  $key            Key name
 * @param bool $up             Whether to increase
 * @param arr  $template_query Item stored on the db
 * @param arr  $obj_data       Original object data
 * @param bool $is_updated     If this is an update
 */
function set_key_freq($key, $up, $template_query, $obj_data, $is_updated) {
    $feedback = array(); // Store the feedback [subkey, method]
    $subkey = key($obj_data->{$key});

    if ($up) {
        if (isset($template_query[$key][$subkey])) {
            ++$template_query[$key][$subkey];
            $feedback = [$subkey, 'incremented'];
        } else {
            $template_query[$key][$subkey] = 1;
        }
    } else {
        if (isset($template_query[$key][$subkey])) {
            if ($template_query[$key][$subkey] > 1) {
                --$template_query[$key][$subkey];
                $feedback = [$subkey, 'decremented'];
            } else {
                unset($template_query[$key][$subkey]);
                $feedback = [$subkey, 'removed'];
            }
        }
    }
    set_data($key, $template_query, $key . ' ' . $feedback[0] . ' ' . $feedback[1], $key . ' ' . $feedback[0] . ' could not be ' . $feedback[1], $is_updated);
}

/**
 * Supports the add/remove increment/decrement of values
 * @param str  $key            The first level key that's being modified
 * @param bool $up             Increment or decrement
 * @param arr  $template_query The item stored on the db
 * @param arr  $obj_data       The original object data
 * @param str  $match          What the key should match
 * @param bool $is_updated     If this is an update
 */
function set_key_qty($key, $up, $template_query, $obj_data, $match, $is_updated) {
    $feedback = array(); // Store the feedback [type, method]
    $item_key = null;
    $item_value = null;
    foreach ($template_query[$key] as $_key => $_value) {
        if ($_key === $match) {
            $item_key = $_key;
            $item_value = $_value;
            break;
        }
    }

    $obj_data_key_arr = (array)$obj_data->{$key};
    $obj_data_key_test = array_filter($obj_data_key_arr);

    if (!empty($obj_data_key_test)) {
        if ($up) {
            if ($item_key) {
                /* Increment value */
                ++$template_query[$key][$item_key]['freq'];
                $feedback = [' frequency', ' incremented'];
            } else {
                $template_query[$key][key($obj_data->{$key})] = $obj_data->{$key}->{key($obj_data->{$key})};
                $feedback = ['', ' added'];
            }
        } else {
            if ($item_key) {
                if ($template_query[$key][$item_key]['freq'] > 1) {
                    /* Decrement value */
                    --$template_query[$key][$item_key]['freq'];
                    $feedback = [' frequency', ' decremented'];
                } else if ($template_query[$key][$item_key]['freq'] === 1) {
                    /* Remove key/value */
                    unset($template_query[$key][$item_key]);
                    $feedback = [' entry', ' removed'];
                }
            } else { return false; }
        }
        set_data($key, $template_query, $key . $feedback[0] . $feedback[1], $key . $feedback[0] . ' could not be' . $feedback[1], $is_updated);
    }
}

/**
 * Increment values in the db
 * @param  arr  $template_query Found template object
 * @param  arr  $obj_data       Original object data
 * @param  bool $is_updated     If this is an update
 */
function increment_template($template_query, $obj_data, $is_updated) {
    set_template_freq(true, $template_query, $is_updated);
    set_key_qty('url', true, $template_query, $obj_data, key($obj_data->url), $is_updated);
    set_key_qty('title', true, $template_query, $obj_data, key($obj_data->title), $is_updated);
    set_key_freq('space', true, $template_query, $obj_data, $is_updated);
    set_key_1_0('input_type', 'auto', 'custom', true, $template_query, $obj_data, $is_updated);
    set_key_1_0('secure', 'no', 'yes', true, $template_query, $obj_data, $is_updated);
}

/**
 * Process the saving of the template data
 * @param  arr  $template_query  Found template object
 * @param  arr  $obj_data        Original object data
 * @param  bool $template_exists Whether template exists
 * @param  bool $is_updated      If this is an update
 */
function save($template_query, $obj_data, $template_exists, $is_updated = false) {
    global $collection;

    if (!$template_exists) {
        /* Insert a new document */
        $inserted = $collection->insert($obj_data);
        set_success($inserted, 'document inserted into the collection', 'document failed to be inserted into the collection');
    } else {
        /* Increment main (template) frequency */
        increment_template($template_query, $obj_data, $is_updated);
    }
}

/**
 * Decrement values in the db
 * @param  arr  $template_query Found template object
 * @param  arr  $obj_data       Original object data
 * @param  bool $is_updated     If this is an update
 */
function decrement_template($template_query, $obj_data, $is_updated) {
    set_template_freq(false, $template_query, $is_updated, $is_updated);
    set_key_qty('url', false, $template_query, $obj_data, key($obj_data->url), $is_updated);
    set_key_qty('title', false, $template_query, $obj_data, key($obj_data->title), $is_updated);
    set_key_freq('space', false, $template_query, $obj_data, $is_updated);
    set_key_1_0('input_type', 'auto', 'custom', false, $template_query, $obj_data, $is_updated);
    set_key_1_0('secure', 'no', 'yes', false, $template_query, $obj_data, $is_updated);
}

/**
 * Process the removal of data
 * @param  arr  $template_query  Found template object
 * @param  arr  $obj_data        Original data object
 * @param  bool $template_exists Whether template exists
 * @param  bool $is_updated      If this is an update
 */
function remove($template_query, $obj_data, $template_exists, $is_updated = false) {
    global $collection;
    global $criteria;

    if ($template_exists) {
        if ($template_query['freq'] > 1) {
            /* Decrement relevant fields */
            decrement_template($template_query, $obj_data, $is_updated);
        } else {
            /* Remove document */
            $removed = $collection->remove($criteria, array('justOne' => true));
            set_success($removed, 'document removed ('. $template_query['template'] .')', 'document could not be removed ('. $template_query['template'] .')');
        }
    }
}

/**
 * Turns a key into a hash with value and freq objects
 * @param str $key      Key to hash
 * @param arr $obj_data Original data object
 */
function set_hashed_key($key, $obj_data) {
    $obj_data_key_arr = (array)$obj_data->{$key};
    $obj_data_key_test = array_filter($obj_data_key_arr);

    if (!empty($obj_data_key_test)) {
        $fetched_key = key($obj_data->{$key});
        $hashed = md5($fetched_key);
        unset($obj_data->{$key}->{$fetched_key});
        $obj_data->{$key}->{$hashed} = array(
            'value' => $fetched_key,
            'freq' => 1
        );
    }
    return $obj_data;
}

function get_hashed_key($url) {
    /* Remove last slash if there */
    $url = rtrim($url, '/');

    /* Remove http:// or https:// */
    $url = ltrim($url, 'https://');
    $url = ltrim($url, 'http://');

    return md5($url);
}