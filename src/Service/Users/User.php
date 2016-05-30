<?php

namespace Garbetjie\WeChatClient\Service\Users;

use DateTime;
use DateTimeZone;

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

    const PROFILE_IMAGE_FULL = 0;
    const PROFILE_IMAGE_46 = 46;
    const PROFILE_IMAGE_64 = 64;
    const PROFILE_IMAGE_96 = 96;
    const PROFILE_IMAGE_132 = 132;

    /**
     * The ID of this user.
     *
     * @var string
     */
    protected $openID;

    /**
     * The nickname this user has given themselves.
     *
     * @var string
     */
    protected $nickname;

    /**
     * An integer designating the user's gender. Refer to the User::MALE, User::FEMALE, and User::UNKNOWN constants.
     *
     * @var int
     */
    protected $gender;

    /**
     * The language this user speaks.
     *
     * @var string
     */
    protected $language;

    /**
     * The city from which this user is from.
     *
     * @var string
     */
    protected $city;

    /**
     * The province from which this user is from.
     *
     * @var string
     */
    protected $province;

    /**
     * The name of the country where this user is from.
     *
     * @var string
     */
    protected $country;

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
    protected $profileImages = [];

    /**
     * If the user is currently subscribed, this will be an instance of the DateTime at which they subscribed.
     *
     * Otherwise, if the user is not subscribed, this will be NULL.
     *
     * @var DateTime|null
     */
    protected $subscribed = null;

    /**
     * Any custom remarks that have been stored against this user.
     *
     * @var string
     */
    protected $remark;

    /**
     * The ID of the group this user belongs to.
     *
     * @var int
     */
    protected $groupID;

    /**
     * User constructor.
     *
     * @param array|object $attributes The attributes that make up the user profile.
     */
    public function __construct ($attributes)
    {
        // Ensure we can handle objects or arrays.
        $attributes = new \ArrayObject($attributes);
        
        // Need to set the OpenID at least.
        $this->openID = (string)$attributes['openid'];

        // User is not subscribed. Do nothing.
        if (! $attributes['subscribe']) {
            return;
        }

        $this->subscribed = new DateTime("@{$attributes['subscribe_time']}", new DateTimeZone('UTC'));
        $this->nickname = (string)$attributes['nickname'];
        $this->gender = (int)$attributes['sex'];
        $this->language = (string)$attributes['language'];
        $this->city = (string)$attributes['city'];
        $this->province = (string)$attributes['province'];
        $this->country = (string)$attributes['country'];
        $this->groupID = (string)$attributes['groupid'];
        $this->remark = (string)$attributes['remark'];

        if (! empty($attributes['headimgurl'])) {
            $this->profileImages[static::PROFILE_IMAGE_FULL] = $attributes['headimgurl']; // Biggest image.

            // Add additional image sizes.
            foreach ([
                 static::PROFILE_IMAGE_46,
                 static::PROFILE_IMAGE_64,
                 static::PROFILE_IMAGE_96,
                 static::PROFILE_IMAGE_132,
            ] as $size) {
                $this->profileImages[$size] = substr(
                    $attributes['headimgurl'],
                    0,
                    strrpos($attributes['headimgurl'], '/')
                ) . '/' . $size;
            }
        }
    }

    /**
     * Returns the user's group ID.
     *
     * @return int
     */
    public function getGroupID ()
    {
        return $this->groupID;
    }

    /**
     * Returns the user's gender. The gender will be returned as one of the User::MALE, User::FEMALE or User::UNKNOWN
     * constants.
     *
     * @return string
     */
    public function getGender ()
    {
        return $this->gender;
    }

    /**
     * Returns a boolean value indicating whether or not the user is subscribed.
     *
     * @return bool
     */
    public function isSubscribed ()
    {
        return $this->subscribed instanceof DateTime;
    }

    /**
     * Returns the `DateTime` at which the user was subscribed.
     * Returns NULL if the user is not subscribed.
     *
     * @return null|DateTime
     */
    public function getSubscribedDate ()
    {
        return $this->subscribed ?: null;
    }

    /**
     * @return string
     */
    public function getOpenID ()
    {
        return $this->openID;
    }

    /**
     * @return string
     */
    public function getNickname ()
    {
        return $this->nickname;
    }

    /**
     * @return string
     */
    public function getLanguage ()
    {
        return $this->language;
    }

    /**
     * @return string
     */
    public function getCity ()
    {
        return $this->city;
    }

    /**
     * @return string
     */
    public function getProvince ()
    {
        return $this->province;
    }

    /**
     * @return string
     */
    public function getCountry ()
    {
        return $this->country;
    }

    /**
     * @return string
     */
    public function getRemark ()
    {
        return $this->remark;
    }

    /**
     * Returns the profile image(s) for the user. The sizes need to be one of the User::PROFILE_IMAGE_* constants.
     * 
     * Can supply an array of sizes (an array of size => URL will be returned), or a single size (will return the URL as
     * a string).
     * 
     * @param array|int $size
     *
     * @return array|string
     */
    public function getProfileImage ($size = User::PROFILE_IMAGE_FULL)
    {
        $returnArray = is_array($size);
        $size = array_values((array)$size);
        $values = array_combine($size, array_pad([], count($size), null));

        foreach ($size as $s) {
            if (array_key_exists($s, $this->profileImages)) {
                $values[$s] = $this->profileImages[$s];
            }
        }
        
        return $returnArray ? $values : $values[$size[0]];
    }
}
