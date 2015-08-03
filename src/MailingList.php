<?php
class MailingList
{
  protected $name;
  // emails that are members of this list
  protected $emails = array();
  // lists that are members of this list
  protected $lists = array();

  public function __construct($name) {
    $this->setName($name);
    $this->get_members();
  }

  public function getName()
  {
    return $this->name;
  }

  public function setName($name)
  {
    $this->name = $name;
  }

  public function getEmails()
  {
    return $this->emails;
  }

  public function getLists()
  {
    return $this->lists;
  }

  private function get_members() {
    $results = array();

    exec("remctl mailman mm who $this->name", $results, $status);

    // the call succeeded
    if ($status == 0) {
      // Remove the blank line at the end, and the X members found line
      array_pop($results);
      array_pop($results);

      foreach ($results as $email) {
        // is the email a list?
        if (preg_match('/lists\.stanford\.edu/', $email)) {
          // only add the part before "lists.stanford.edu"
          $this->lists[] = preg_replace('/(.*)\@lists\.stanford\.edu/', '$1', $email);
        } else {
          // sometimes lists include the name of the subscriber
          // in that case, the email is in angle brackets
          $this->emails[] = preg_replace('/(.*)<(.*)>/', '$2', $email);
        }
      }
    }
  }
}
