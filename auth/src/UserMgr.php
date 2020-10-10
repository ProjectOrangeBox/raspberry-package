<?php

namespace projectorangebox\auth;

use projectorangebox\auth\User;

class UserMgr implements UserMgrInterface
{
  protected $config = [];

  public function __construct(array $config)
  {
    $this->config = array_replace(require __DIR__ . '/config.php', $config);
  }

  public function createUser($username, $email, $password, $emailActivation)
  {
  }

  public function loadUser($userId): User
  {
  }

  public function isUsernameAvailable($username)
  {
  }

  public function isEmailAvailable($email)
  {
  }

  public function activateUser($userId, $activationKey, $activateByEmail = true)
  {
  }

  public function forgotPassword($login)
  {
  }

  public function canResetPassword($userId, $newPasswordKey)
  {
  }

  public function resetPassword($userId, $newPasswordKey, $newPassword)
  {
  }

  public function changePassword($oldPassword, $newPassword)
  {
  }

  public function setNewEmail($newEmail, $password)
  {
  }

  public function activateNewEmail($userId, $newEmailKey)
  {
  }

  public function deleteUser($password)
  {
  }

  public function getErrorMessage()
  {
  }
} /* end class */
