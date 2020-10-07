<?php

namespace projectorangebox\dispatcher;

use projectorangebox\dispatcher\Controller;

abstract class JsonRestController extends Controller
{
  /**
   * Present a view of resource objects
   *
   * @return string
   */
  public function index()
  {
    return $this->sendNotImplemented(['method' => 'index']);
  }

  /**
   * Present a view to present a specific resource object
   *
   * @param  type $id
   * @return string
   */
  public function show($id = null)
  {
    return $this->sendNotImplemented(['method' => 'show/' . $id]);
  }

  /**
   * Present a view to present a new single resource object
   *
   * @return string
   */
  public function new()
  {
    return $this->sendNotImplemented(['method' => 'new']);
  }

  /**
   * Process the creation/insertion of a new resource object.
   * This should be a POST.
   *
   * @return string
   */
  public function create()
  {
    return $this->sendNotImplemented(['method' => 'create']);
  }

  /**
   * Present a view to confirm the deletion of a specific resource object
   *
   * @param  type $id
   * @return string
   */
  public function remove($id = null)
  {
    return $this->sendNotImplemented(['method' => 'remove/' . $id]);
  }

  /**
   * Process the deletion of a specific resource object
   *
   * @param  type $id
   * @return string
   */
  public function delete($id = null)
  {
    return $this->sendNotImplemented(['method' => 'delete/' . $id]);
  }

  /**
   * Present a view to edit the properties of a specific resource object
   *
   * @param  type $id
   * @return string
   */
  public function edit($id = null)
  {
    return $this->sendNotImplemented(['method' => 'edit/' . $id]);
  }

  /**
   * Process the updating, full or partial, of a specific resource object.
   * This should be a POST.
   *
   * @param  type $id
   * @return string
   */
  public function update($id = null)
  {
    return $this->sendNotImplemented(['method' => 'update/' . $id]);
  }

  /* protected responses */

  protected function sendCreated(array $data = null)
  {
    $this->send(201, $data);
  }

  protected function sendDeleted(array $data = null)
  {
    $this->send(200, $data);
  }

  protected function sendUpdated(array $data = null)
  {
    $this->send(200, $data);
  }

  protected function sendNoContent(array $data = null)
  {
    $this->send(204, $data);
  }

  protected function sendInvalidRequest(array $data = null)
  {
    $this->send(400, $data);
  }

  protected function sendAccessDenied(array $data = null)
  {
    $this->send(401, $data);
  }

  protected function sendResourceNotFound(array $data = null)
  {
    $this->send(404, $data);
  }

  protected function sendConflict(array $data = null)
  {
    $this->send(409, $data);
  }

  protected function sendServerError(array $data = null)
  {
    $this->send(500, $data);
  }

  protected function sendNotImplemented(array $data = null)
  {
    $this->send(501, $data);
  }

  protected function send(int $status, array $data = null)
  {
    $data = ($data) ? json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) : '{}';

    $this->response->set($data)->responseCode($status)->contentType('application/json');
  }
} /* end class */
