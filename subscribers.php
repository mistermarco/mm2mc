<?php

require_once __DIR__ . '/bootstrap.php';

$message = "Running Mailman to MailChimp Script...\n";

$lists = $config['lists'];

// Create a new queue and fill it with the lists
$list_queue = new SourceList();
$list_queue->enqueue($lists);


$subscribers = array();

// Loop through all the lists (and any lists found inside)
while ($list_queue->isEmpty() === FALSE) {

  // get the listname from the queue
  $list_name = $list_queue->dequeue();

  $list = new MailingList($list_name);

  // Add any new emails to the list of subscribers
  $subscribers = array_merge($list->getEmails(), $subscribers);

  // Remove any duplicates
  $subscribers = array_unique($subscribers);

  // If there are any lists in the membership, add them to the queue
  foreach ($list->getLists() as $new_list) {
    $list_queue->enqueue($new_list);
  }
}

// Save the members to the database

$new_subscribers = array();

foreach ($subscribers as $address) {
  // find out if the email already exists
  $existing_email = $em->getRepository('Email')->findByEmail($address);

  // if it's a new email
  if (!count($existing_email)) {
    try {
	  // add it to MailChimp's list
      $new_email = ['email_address' => $address, 'status' => 'subscribed'];
      $body = json_encode($new_email);
      $response = $client->post("lists/$list_id/members", ['body' => $body]);
      $body = json_decode($response->getBody());

	  // add it to the database
      $email = new Email($address);
      $em->persist($email);

	  // keep track of new subscribers
	  $new_subscribers[] = $address;
      $message .= $body->email_address . ' has been ' . $body->status . "\n";
    } catch (GuzzleHttp\Exception\ClientException $e) {
      // Return any errors
      $code   = $e->getResponse()->getStatusCode();
      $phrase = $e->getResponse()->getReasonPhrase();
	  $message .="$code: $phrase\n";

	  // MailChimp will return a 400 if the email is already in the list
	  // subscribed or unsubscribed
	  if ($code == 400) {
        $body = json_decode($e->getResponse()->getBody());
	    $message .= $body->detail . "\n";
        if (preg_match('/already a list member/', $body->detail)) {
		  // user was subscribed in some other way, let's add to the database
		  $email = new Email($address);
          $em->persist($email);
	    }
	  }
    } catch (GuzzleHttp\Exception\ServerException $e) {
      $code   = $e->getResponse()->getStatusCode();
      $phrase = $e->getResponse()->getReasonPhrase();
      $message .= "Woah. Something is wrong in the land of MailChimp.\n";
	  $message .= "Status: $code\nReason: $phrase\n";
	  $message .= "Exiting. Try again later.\n";
	  exit;
	}
  }
}

// Save everything to the database
$em->flush();

$new_count = 0;

// Print out the results
foreach ($new_subscribers as $email) {
  $new_count++;
  $message .= $email . "\n";
}

if ($new_count == 0) {
  $message .= "No new subscribers today. :(";
}

$slack->sendMessage($message);

echo $message;
