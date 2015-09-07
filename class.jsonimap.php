<?php

/**
 * 
 */
namespace JsonImap;

/**
 * 
 */
class JsonImap
{
  
  /**
   * [$server description]
   * @var [type]
   */
  private $server;

  /**
   * [$login description]
   * @var [type]
   */
  private $login;

  /**
   * [$password description]
   * @var [type]
   */
  private $password;

  /**
   * [$stream description]
   * @var [type]
   */
  private $stream;

  /**
   * @param [type]
   * @param [type]
   * @param [type]
   */
  function __construct($server, $login, $password)
  {
    $this->server = $server;
    $this->login = $login;
    $this->password = $password;
    $this->stream = imap_open($this->server, $this->login, $this->password);
  }

  /**
   * @return [type]
   */
  public function getFolders()
  {
    $folders = [];
    $mailboxes = imap_list($this->stream, $this->server, "*");
    foreach ($mailboxes as $mailbox) {
      $folders[] = str_replace($this->server, "", $mailbox);
    }
    return $folders;
  }

  /**
   * [newFolder description]
   * @param  [type] $name [description]
   * @return [type]       [description]
   */
  public function newFolder($name)
  {
    if (imap_createmailbox($this->stream, $this->server . $name)) {
      return true;
    }
  }

  /**
   * [updateFolder description]
   * @param  [type] $oldName [description]
   * @param  [type] $newName [description]
   * @return [type]          [description]
   */
  public function updateFolder($oldName, $newName)
  {
    if (imap_renamemailbox($this->stream, $this->server . $oldName, $this->server . $newName)) {
      return true;
    }
  }

  /**
   * [deleteFolder description]
   * @param  [type] $folder [description]
   * @return [type]         [description]
   */
  public function deleteFolder($folder)
  {
    if (imap_deletemailbox($this->stream, $this->server . $folder)) {
      return true;
    }
  }

  /**
   * @param  [type]
   * @return [type]
   */
  public function getOverview($folder)
  {
    imap_reopen($this->stream, $this->server . $folder);
    $mailboxCheck = imap_check($this->stream);
    if ($mailboxCheck->Nmsgs == 0) {
      return [];
    } else {
      $folderOverview = imap_fetch_overview($this->stream, "1:{$mailboxCheck->Nmsgs}", 0);  
      return $folderOverview;
    }
  }

  /**
   * @return [type]
   */
  public function getFullOverview()
  {
    $fullOverview = [];
    $folders = $this->getFolders();
    foreach ($folders as $value) {
      $fullOverview[$value] = $this->getOverview($value);
    }
    return $fullOverview;
  }

  /**
   * @param  [type]
   * @param  [type]
   * @return [type]
   */
  public function getItem($folder, $id)
  {
    imap_reopen($this->stream, $this->server . $folder);
    // "1" for plain text, "2" for HTML
    $item = imap_fetchbody($this->stream, $id, "1");
    return $item;
  }

  /**
   * [moveItem description]
   * @param  [type] $items     [description]
   * @param  [type] $oldFolder [description]
   * @param  [type] $newFolder [description]
   * @return [type]            [description]
   */
  public function moveItem($items, $oldFolder, $newFolder)
  {
    imap_reopen($this->stream, $this->server . $oldFolder);
    $itemList = implode(',', $items);
    if (imap_mail_move($this->stream, "{$itemList}", $newFolder, CP_UID)) {
      return true;
    }
  }

  /**
   * [deleteItem description]
   * @param  [type] $items  [description]
   * @param  [type] $folder [description]
   * @return [type]         [description]
   */
  public function deleteItem($items, $folder)
  {
    imap_reopen($this->stream, $this->server . $folder);
    $itemList = implode(',', $items);
    if (imap_delete($this->stream, "{$itemList}", FT_UID)) {
      return true;
    }
  }

  /**
   * @param  [type]
   * @param  [type]
   * @return [type]
   */
  public function getHeader($folder, $id)
  {
    imap_reopen($this->stream, $this->server . $folder);
    $header = imap_fetchheader($this->stream, $id);
    return $header;
  }

  /**
   * @return [type]
   */
  public function getQuota()
  {
    $folders = $this->getFolders();
    $quota = imap_get_quotaroot($this->stream, $folders[0]);
    return $quota;
  }

  /**
   * 
   */
  function __destruct()
  {
    imap_expunge($this->stream);
    imap_close($this->stream);
  }

}