<?php
/**
 * @Entity @Table(name="email")
 **/
class Email
{
  /** @Id @Column(type="integer") @GeneratedValue **/
  protected $id;
  /** @Column(type="string") **/
  protected $email;

  public function __construct($email = '') {
    $this->setEmail($email);
  }

  public function getId()
  {
    return $this->id;
  }

  public function getEmail()
  {
    return $this->email;
  }

  public function setEmail($email) 
  {
    $this->email = $email;
  }
}
