<?php

namespace Garbetjie\WeChatClient\Messaging;

class TemplateMessageFormatter
{
    /**
     * Formats the given data to be used in a template message. Returns an array that can be sent to the API.
     * 
     * @param string $templateID
     * @param string $recipientOpenID
     * @param string $url
     * @param array  $data
     * @param array  $options
     *
     * @return array
     */
    public function format ($templateID, $recipientOpenID, $url, array $data, array $options = [])
    {
        $json = [
            'touser'      => (string)$recipientOpenID,
            'template_id' => (string)$templateID,
            'url'         => (string)$url,
            'data'        => [],
        ];

        if (isset($options['color']) && static::validateColor($options['color'])) {
            $json['topcolor'] = '#' . strtoupper(ltrim($options['color'], '#'));
        }

        foreach ($data as $fieldName => $fieldValue) {
            if (! is_array($fieldValue)) {
                $json['data'][$fieldName] = ['value' => $fieldValue];
            } else {
                $json['data'][$fieldName] = [
                    'value' => $fieldValue['value'],
                ];

                if (isset($fieldValue['color']) && static::validateColor($fieldValue['color'])) {
                    $json['data'][$fieldName]['color'] = '#' . strtoupper(ltrim($fieldValue['color'], '#'));
                }
            }
        }
        
        return $json;
    }

    /**
     * Returns a boolean value indicating whether or not the supplied colour is a valid hexadecimal colour.
     * 
     * @param string $color
     *
     * @return bool
     */
    static protected function validateColor ($color)
    {
        return preg_match('/^(#)?([a-f0-9]{3}|[a-f0-9]{6})$/i', $color) > 0;
    }
}
