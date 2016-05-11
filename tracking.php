<?php
include_once 'store.connect.php';

$success = 1;

for($i=0; $i < count($data); $i++) {
    if($data[$i]->type == 'event') {
        $document = array(
            'type'      => 'event',
            'user'      => $data[$i]->user,
            'event'     => $data[$i]->event,
            'value'     => $data[$i]->value,
            'timestamp' => $data[$i]->timestamp
        );

        try {
            $collection->insert($document);
        }
        catch (MongoCursorException $mce) {
            $success = 0;
        }
        catch (MongoCursorTimeoutException $mcte) {
            $success = 0;
        }
    }

    elseif($data[$i]->type == 'prop') {
        $document = $collection->findOne(array('user' => $data[$i]->user, 'prop' => $data[$i]->prop));
        if(is_null($document)) {
            $document = array(
                'type'      => 'prop',
                'user'      => $data[$i]->user,
                'prop'      => $data[$i]->prop,
                'value'     => $data[$i]->value
            );

            try {
                $collection->insert($document);
            }
            catch (MongoCursorException $mce) {
                $success = 0;
            }
            catch (MongoCursorTimeoutException $mcte) {
                $success = 0;
            }
        }
        else {
            try {
                $collection->update(array('user' => $data[$i]->user, 'prop' => $data[$i]->prop), array('$set' => array('value' => $data[$i]->value)));
            }
            catch (MongoCursorException $mce) {
                $success = 0;
            }
            catch (MongoCursorTimeoutException $mcte) {
                $success = 0;
            }
        }
    }

    echo json_encode(array(
        'success'   => $success
    ));
}
