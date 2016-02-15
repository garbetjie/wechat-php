<?php

namespace WeChat\Users;

use DateTime;

class User
{
    const MALE = 'male';
    const FEMALE = 'female';
    const UNKNOWN = 'unknown';

    /**
     * @var array
     */
    protected $attributes = [ ];

    /**
     * User constructor.
     *
     * @param array $attributes The attributes that make up the user profile.
     */
    public function __construct ( array $attributes )
    {
        $this->attributes = $attributes;
    }

    /**
     * @return string
     */
    public function id ()
    {
        return $this->_get( 'openid' );
    }

    /**
     * @return null|string
     */
    public function nickname ()
    {
        return $this->_get( 'nickname' );
    }

    /**
     * Returns one of the User::* gender constants, indicating the gender of the user.
     *
     * @return string
     */
    public function gender ()
    {
        switch ( $this->_get( 'sex' ) ) {
            case 1:
                return static::MALE;

            case 2:
                return static::FEMALE;

            default:
                return static::UNKNOWN;
        }
    }

    /**
     * @return bool
     */
    public function subscribed ()
    {
        return !! $this->_get( 'subscribe' );
    }

    /**
     * @return int|null
     */
    public function group ()
    {
        return $this->_get( 'groupid' );
    }

    /**
     * @return null|string
     */
    public function language ()
    {
        return $this->_get( 'language' );
    }

    /**
     * @return null|string
     */
    public function city ()
    {
        return $this->_get( 'city' );
    }

    /**
     * @return null|string
     */
    public function province ()
    {
        return $this->_get( 'province' );
    }

    /**
     * @return null|string
     */
    public function country ()
    {
        return $this->_get( 'country' );
    }

    /**
     * @return DateTime
     */
    public function created ()
    {
        return DateTime::createFromFormat( 'U', (int) $this->_get( 'subscribe_time' ) ?: 0 ); 
    }

    /**
     * @return null|string
     */
    public function remark ()
    {
        return $this->_get( 'remark' );
    }

    /**
     * @param $key
     *
     * @return mixed|null
     */
    protected function _get( $key )
    {
        return array_key_exists( $key, $this->attributes ) ? $this->attributes[ $key ] : null;
    }
}
