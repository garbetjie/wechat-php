<?php

namespace Garbetjie\WeChatClient\Service\Users;

use DateTime;

/**
 * @method string nickname()
 * @method string city()
 * @method string province()
 * @method string country()
 * @method string language()
 * @method string id()
 * @method string remark()
 */
class User
{
    const MALE = 1;
    const FEMALE = 2;
    const UNKNOWN = 0;

    /**
     * The ID of this user.
     * 
     * @var string
     */
    public $id;

    /**
     * The nickname this user has given themselves.
     * 
     * @var string
     */
    public $nickname;

    /**
     * An integer designating the user's gender. Refer to the User::MALE, User::FEMALE, and User::UNKNOWN constants.
     * @var int
     */
    public $gender;

    /**
     * The language this user speaks.
     * 
     * @var string
     */
    public $language;

    /**
     * The city from which this user is from.
     * 
     * @var string
     */
    public $city;

    /**
     * The province from which this user is from.
     * 
     * @var string
     */
    public $province;

    /**
     * The name of the country where this user is from.
     * 
     * @var string
     */
    public $country;

    /**
     * An array containing the profile image sizes that are available for this user. The default (and biggest) size is
     * indexed 0.
     * 
     * The available sizes are:
     * 
     * - 0   => The biggest size (max of 640x640)
     * - 46  => 46x46 square
     * - 64  => 64x64 square
     * - 96  => 96x96 square
     * - 132 => 132x132 square
     * 
     * @var array
     */
    public $profileImages = [];

    /**
     * If the user is currently subscribed, this will be an instance of the DateTime at which they subscribed.
     * 
     * Otherwise, if the user is not subscribed, this will be NULL.
     * 
     * @var DateTime|null
     */
    public $subscribed = null;

    /**
     * Any custom remarks that have been stored against this user.
     * 
     * @var string
     */
    public $remark;

    /**
     * The ID of the group this user belongs to.
     * 
     * @var int
     */
    public $groupID;

    /**
     * User constructor.
     *
     * @param array $attributes The attributes that make up the user profile.
     */
    public function __construct (array $attributes)
    {
        if (empty($attributes['subscribe'])) {
            return;
        }

        $this->subscribed = DateTime::createFromFormat('U', $attributes['subscribe_time'], new \DateTimeZone('UTC'));
        $this->id = (string)$attributes['openid'];
        $this->nickname = (string)$attributes['nickname'];
        $this->gender = (int)$attributes['sex'];
        $this->language = (string)$attributes['language'];
        $this->city = (string)$attributes['city'];
        $this->province = (string)$attributes['province'];
        $this->country = (string)$attributes['country'];
        $this->groupID = (string)$attributes['groupid'];
        $this->remark = (string)$attributes['remark'];
        
        if (!empty($attributes['headimgurl'])) {
            $this->profileImages[0] = $attributes['headimgurl']; // Biggest image.
            
            // Add additional image sizes.
            foreach ([46, 64, 96, 132] as $size) {
                $this->profileImages[$size] = substr($attributes['headimgurl'], 0, strrpos($attributes['headimgurl'], '/')) . '/' . $size;
            }
        }
    }

    /**
     * Magic method to provide backwards-compatible access to the user's properties.
     * 
     * @param string $name
     * @param array $arguments
     * 
     * @throws \BadMethodCallException
     */
    public function __call ($name, $arguments)
    {
        if (isset($this->{$name})) {
            return $this->{$name};
        }
        
        throw new \BadMethodCallException("Unknown method '{$name}'");
    }

    /**
     * Backwards-compatible accessor for the user's group.
     *
     * @deprecated
     * @return int
     */
    public function group ()
    {
        return $this->groupID;
    }

    /**
     * Backwards-compatible accessor for returning the gender as a string. Ideally, it should be using the class constants.
     *
     * @deprecated
     * @return string
     */
    public function gender ()
    {
        switch ($this->gender) {
            case self::MALE:
                return 'male';
            
            case self::FEMALE:
                return 'female';
            
            default:
                return 'unknown';
        }
    }

    /**
     * Backwards-compatible accessor for returning whether the subscriber is subscribed or not.
     *
     * @deprecated
     * @return bool
     */
    public function subscribed ()
    {
        return $this->subscribed instanceof DateTime;
    }

    /**
     * @deprecated 
     * @return DateTime
     */
    public function created ()
    {
        return $this->subscribed ?: new DateTime(0);
    }
}
