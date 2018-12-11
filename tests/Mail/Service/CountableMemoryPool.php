<?php

namespace App\Tests\Mail\Service;

/**
 * MemoryPool to count the sent messages
 */
class CountableMemoryPool extends \Swift_MemorySpool implements \Countable
{
    public function count()
    {
        return count($this->messages);
    }


    /**
     * @return \Swift_Mime_SimpleMessage[]
     */
    public function getMessages() : array
    {
        return $this->messages;
    }

}