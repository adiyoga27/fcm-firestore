<?php

require_once('vendor/autoload.php');

use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Contract\Firestore;

header("Access-Control-Allow-Origin: *"); //allow cors
header("Content-Type: application/json");
$method = $_SERVER['REQUEST_METHOD'];

if ($method === "POST") {

    $fcm = (new Factory)
        ->withServiceAccount('firebase_credentials.json');

    $imageUrl = $_POST['image'];
    $title = $_POST['title'];
    $body = $_POST['body'];
    $type = $_POST['type'];
    $action = $_POST['action'];
    $topic = $_POST['topic'];

    try {

        if ($type != 'Broadcast') {
            $firestore = $fcm->createFirestore();
            print_r($firestore);

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
        print_r($th);
    }
}
