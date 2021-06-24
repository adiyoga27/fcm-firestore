<?php

require_once('vendor/autoload.php');

use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\Topic;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Firestore;
use Google\Cloud\Firestore\FirestoreClient;

$fcm = (new Factory)
    ->withServiceAccount('firebase_credentials.json');
$firestore = (new Factory)->withServiceAccount('firebase_credentials.json')->createFirestore();


header("Access-Control-Allow-Origin: *"); //allow cors
header("Content-Type: application/json");
$method = $_SERVER['REQUEST_METHOD'];

if ($method === "POST") {
    $imageUrl = $_POST['image'];
    $title = $_POST['title'];
    $body = $_POST['body'];
    $type = $_POST['type'];
    $action = $_POST['action'];
    $topic = $_POST['topic'];

    try {

        if ($type != 'Broadcast') {
            // Menyiman di Firestore
            $db = $firestore->database();
            $row = [];
            for ($i = 2; $i < 4; $i++) {
                $data = [
                    'action' => 'promo',
                    'isActive' => true,
                    'message' => $body,
                    'topic' => '112211000' . $i
                ];
                if ($action == 'promo') {
                    $data['data'] = [
                        'isRead' => false,
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        'id' => 27
                    ];
                }
                $row[] = $data;
            }

            $batch = $db->batch();
            foreach ($row as $value) {
                $batch->set($db->collection('notification')->add(), $value);
            }
            $result = $batch->commit();
            print_r($result);
        }


        //Melakukan Send Notification
        $notification = Notification::fromArray([
            'title' => $title,
            'body' => $body,
            'image' => $imageUrl,
        ]);

        $message = CloudMessage::withTarget('topic', $topic)
            ->withNotification($notification);
        $messaging = $fcm->createMessaging();
        $messaging->send($message);
    } catch (\InvalidArgumentException $th) {
        echo $th;
    }
}
