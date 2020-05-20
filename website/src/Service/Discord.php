<?php

namespace App\Service;

use RestCord\DiscordClient;

class Discord
{
    const ROLE_FORMAT = '<@&%s> %s';
    
    private function discord(): DiscordClient
    {
        $token = trim(file_get_contents(__DIR__.'/DiscordToken'));
        
        return new DiscordClient([
            'token' => $token
        ]);
    }
    
    /**
     * Send a message to a channel
     */
    public function sendMessage(int $channel, string $content = null, $embed = null)
    {
        $options = [
            'channel.id' => (int)$channel,
        ];
        
        if ($content) {
            $options['content'] = $content;
        }
        
        if ($embed) {
            $options['embed'] = json_decode(json_encode($embed), true);
        }
    
        $this->discord()->channel->createMessage($options);
    }
            /**
     * Send a direct message
     */
    public function sendDirectMessage(int $user, string $content = null, $embed = null)
    {
        $dm = $this->discord()->user->createDm([
            'recipient_id' => (int)$user,
        ]);
        
        $options = [
            'channel.id' => (int)$dm->id,
        ];
        
        if ($content) {
            $options['content'] = $content;
        }
        
        if ($embed) {
            $options['embed'] = json_decode(json_encode($embed), true);
        }
    
        $this->discord()->channel->createMessage($options);
    }
}
