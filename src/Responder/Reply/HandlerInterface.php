<?php

namespace Garbetjie\WeChatClient\Responder\Reply;

use Garbetjie\WeChatClient\Messaging\Type;

interface HandlerInterface
{
    public function sendText (Type\Text $message);

    public function sendImage (Type\Image $imageMessage);

    public function sendAudio (Type\Audio $audioMessage);

    public function sendVideo (Type\Video $videoMessage);

    public function sendMusic (Type\Music $musicMessage);

    public function sendNews (Type\News $newsMessage);
    
    public function sendEchoString ($echoString);
}
