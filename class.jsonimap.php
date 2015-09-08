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
  protected $server;

  /**
   * [$login description]
   * @var [type]
   */
  protected $login;

  /**
   * [$password description]
   * @var [type]
   */
  protected $password;

  /**
   * [$stream description]
   * @var [type]
   */
  protected $stream;

  /**
   * [__construct description]
   * @param [type] $server   [description]
   * @param [type] $login    [description]
   * @param [type] $password [description]
   */
  function __construct($server, $login, $password)
  {
    $this->server = $server;
    $this->login = $login;
    $this->password = $password;
    $this->stream = imap_open($this->server, $this->login, $this->password);
  }

  /**
   * [getMailboxes description]
   * @return [type] [description]
   */
  public function getMailboxes()
  {
    $mailboxes = [];
    $remoteMailboxes = imap_list($this->stream, $this->server, "*");
    foreach ($remoteMailboxes as $remoteMailbox) {
      $mailboxes[] = str_replace($this->server, "", $remoteMailbox);
    }
    return $mailboxes;
  }

  /**
   * [newMailbox description]
   * @param  [type] $name [description]
   * @return [type]       [description]
   */
  public function newMailbox($name)
  {
    if (imap_createmailbox($this->stream, $this->server . $name)) {
      return true;
    }
  }

  /**
   * [updateMailbox description]
   * @param  [type] $oldName [description]
   * @param  [type] $newName [description]
   * @return [type]          [description]
   */
  public function updateMailbox($oldName, $newName)
  {
    if (imap_renamemailbox($this->stream, $this->server . $oldName, $this->server . $newName)) {
      return true;
    }
  }

  /**
   * [deleteMailbox description]
   * @param  [type] $mailbox [description]
   * @return [type]          [description]
   */
  public function deleteMailbox($mailbox)
  {
    if (imap_deletemailbox($this->stream, $this->server . $mailbox)) {
      return true;
    }
  }

  /**
   * [getOverview description]
   * @param  [type] $mailbox [description]
   * @return [type]          [description]
   */
  public function getOverview($mailbox)
  {
    imap_reopen($this->stream, $this->server . $mailbox);
    $mailboxCheck = imap_check($this->stream);
    if ($mailboxCheck->Nmsgs == 0) {
      return [];
    } else {
      $mailboxOverview = imap_fetch_overview($this->stream, "1:{$mailboxCheck->Nmsgs}", 0);  
      return $mailboxOverview;
    }
  }

  /**
   * [getFullOverview description]
   * @return [type] [description]
   */
  public function getFullOverview()
  {
    $fullOverview = [];
    $mailboxes = $this->getMailboxes();
    foreach ($mailboxes as $mailbox) {
      $fullOverview[$mailbox] = $this->getOverview($mailbox);
    }
    return $fullOverview;
  }

  /**
   * [getItem description]
   * @param  [type] $mailbox [description]
   * @param  [type] $id      [description]
   * @return [type]          [description]
   */
  public function getItem($mailbox, $id)
  {
    imap_reopen($this->stream, $this->server . $mailbox);
    // "1" for plain text, "2" for HTML
    $item = imap_fetchbody($this->stream, $id, "1");
    return $item;
  }

  /**
   * [moveItem description]
   * @param  [type] $items      [description]
   * @param  [type] $oldMailbox [description]
   * @param  [type] $newMailbox [description]
   * @return [type]             [description]
   */
  public function moveItem($items, $oldMailbox, $newMailbox)
  {
    imap_reopen($this->stream, $this->server . $oldMailbox);
    $itemList = implode(',', $items);
    if (imap_mail_move($this->stream, "{$itemList}", $newMailbox, CP_UID)) {
      return true;
    }
  }

  /**
   * [deleteItem description]
   * @param  [type] $items   [description]
   * @param  [type] $mailbox [description]
   * @return [type]          [description]
   */
  public function deleteItem($items, $mailbox)
  {
    imap_reopen($this->stream, $this->server . $mailbox);
    $itemList = implode(',', $items);
    if (imap_delete($this->stream, "{$itemList}", FT_UID)) {
      return true;
    }
  }

  /**
   * [getHeader description]
   * @param  [type] $mailbox [description]
   * @param  [type] $id      [description]
   * @return [type]          [description]
   */
  public function getHeader($mailbox, $id)
  {
    imap_reopen($this->stream, $this->server . $mailbox);
    $header = imap_fetchheader($this->stream, $id);
    return $header;
  }

  /**
   * [getQuota description]
   * @return [type] [description]
   */
  public function getQuota()
  {
    $mailboxes = $this->getMailboxes();
    $quota = imap_get_quotaroot($this->stream, $mailboxes[0]);
    return $quota;
  }

  /**
   * [__destruct description]
   */
  function __destruct()
  {
    imap_expunge($this->stream);
    imap_close($this->stream);
  }

}