<?php

namespace Garbetjie\WeChatClient\Service\Menu;

use Garbetjie\WeChatClient\Exception\ApiErrorException;
use Garbetjie\WeChatClient\Service;
use Garbetjie\WeChatClient\Service\Menu\Exception;
use Garbetjie\WeChatClient\Service\Menu\Menu;
use Garbetjie\WeChatClient\Service\Menu\MenuItem;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use InvalidArgumentException;
use Garbetjie\WeChatClient\Client;

class MenuService extends Service
{
    /**
     * Creates a menu for the OA.
     *
     * @param Menu $menu
     *
     * @throws GuzzleException
     * @throws ApiErrorException
     */
    public function create (Menu $menu)
    {
        $json = json_encode($this->reduceMenu($menu));
        $request = new Request('POST', "https://api.weixin.qq.com/cgi-bin/menu/create", [], $json);
        $this->client->send($request);
    }

    /**
     * Deletes the current menu in the OA.
     *
     * @throws GuzzleException
     * @throws ApiErrorException
     */
    public function delete ()
    {
        $request = new Request('GET', "https://api.weixin.qq.com/cgi-bin/menu/delete");
        $this->client->send($request);
    }

    /**
     * Retrieve the current menu for the OA, and return it as an instance of `Garbetjie\WeChatClient\Menu\Menu`.
     *
     * @return Menu
     * 
     * @throws GuzzleException
     * @throws ApiErrorException
     * @throws Exception
     */
    public function fetch ()
    {
        try {
            $request = new Request("GET", "https://api.weixin.qq.com/cgi-bin/menu/get");
            $response = $this->client->send($request);
            $json = json_decode((string)$response->getBody(), true);

            return $this->inflateMenu($json);
        } catch (InvalidArgumentException $e) {
            throw new Exception("cannot build fetched menu. invalid JSON.", null, $e);
        }
    }

    /**
     * Validates the supplied menu, and returns a boolean value indicating whether or not the menu *should* be a valid
     * menu according to the API.
     *
     * @param Menu $menu
     *
     * @return bool
     */
    public function validates (Menu $menu)
    {
        foreach ($menu->items() as $item) {
            if (! $this->validateItem($item)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validates whether the supplied item qualifies as a valid menu item.
     *
     * @param MenuItem $item
     *
     * @return bool
     */
    protected function validateItem (MenuItem $item)
    {
        // MenuItem has children.
        if (count($item->children()) > 0) {
            foreach ($item->children() as $childItem) {
                if (! $this->validateItem($childItem)) {
                    return false;
                }
            }

            // If there are children, the title cannot be longer than 16 characters.
            if (strlen($item->title()) > 16) {
                return false;
            }
            
            return true;
        }

        // Title cannot be longer than 40 characters.
        if (strlen($item->title()) > 40) {
            return false;
        }

        // URLs cannot be longer than 256 characters.
        if ($item->type() == MenuItem::URL && strlen($item->key()) > 256) {
            return false;
        } elseif (strlen($item->key()) > 128) {
            return false;
        } elseif (strlen($item->key()) < 1) {
            return false;
        }

        return true;
    }

    /**
     * Reduces the supplied menu into its array equivalent, ready for sending to the WeChat API.
     *
     * @param Menu $menu
     *
     * @return array
     */
    protected function reduceMenu (Menu $menu)
    {
        $reduced = ['button' => []];

        foreach ($menu->items() as $menuItem) {
            $reduced['button'][] = $this->reduceItem($menuItem);
        }

        return $reduced;
    }

    /**
     * Reduces a menu item, and returns it as an array that can be used in a request to the WeChat API.
     *
     * @param MenuItem $item
     *
     * @return array
     */
    protected function reduceItem (MenuItem $item)
    {
        $reduced = ['name' => $item->title()];

        // Has children.
        if (count($item->children()) > 0) {
            $reduced['sub_button'] = [];
            foreach ($item->children() as $childItem) {
                $reduced['sub_button'][] = $this->reduceItem($childItem);
            }
        } // No children.
        else {
            $reduced['type'] = $item->type();
            $reduced[$item->type() === MenuItem::URL ? 'url' : 'key'] = $item->key();
        }

        return $reduced;
    }

    /**
     * Receives an array representation of a menu, and returns it as an instance of `WeChat\Menu\Menu`.
     *
     * @param array $deflated
     *
     * @return Menu
     * 
     * @throws InvalidArgumentException
     */
    protected function inflateMenu ($deflated)
    {
        if (! isset($deflated['menu']['button'])) {
            throw new InvalidArgumentException('Unexpected menu JSON structure: `menu.button` not found');
        }

        $menu = new Menu();

        foreach ($deflated['menu']['button'] as $item) {
            $menu->add($this->inflateItem($item));
        }

        return $menu;
    }

    /**
     * Inflate the given array representation of an item, and return it as an instance of `Garbetjie\WeChatClient\Menu\MenuItem`.
     *
     * @param array $item
     *
     * @return MenuItem
     */
    protected function inflateItem (array $item)
    {
        if (isset($item['sub_button']) & count($item['sub_button']) > 0) {
            $object = new MenuItem($item['name'], MenuItem::KEYWORD);
            foreach ($item['sub_button'] as $subItem) {
                $object->add($this->inflateItem($subItem));
            }
        } else {
            if (isset($item['url'])) {
                $object = new MenuItem($item['name'], $item['type'], $item['url']);
            } elseif (isset($item['key'])) {
                $object = new MenuItem($item['name'], $item['type'], $item['key']);
            } else {
                $object = new MenuItem($item['name'], $item['type']);
            }
        }

        return $object;
    }
}
