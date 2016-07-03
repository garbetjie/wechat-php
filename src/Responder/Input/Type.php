<?php

namespace Garbetjie\WeChatClient\Responder\Input;

final class Type
{
    const AUDIO = 'voice';
    const IMAGE = 'image';
    const LINK = 'link';
    const LOCATION = 'location';
    const TEXT = 'text';
    const VIDEO = 'video';
    const EVENT = 'event';
    const ALL = '*';
}
