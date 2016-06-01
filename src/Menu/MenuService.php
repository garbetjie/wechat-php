<?php

namespace Garbetjie\WeChatClient\Menu;

use Garbetjie\WeChatClient\Menu\Exception\MenuException;
use Garbetjie\WeChatClient\Service;
use GuzzleHttp\Psr7\Request;

class MenuService extends Service
{
    /**
     * Creates a menu for the OA.
     *
     * @param Menu $menu
     */
    public function saveMenu (Menu $menu)
    {
        $this->client->send(
            new Request(
                'POST',
                "https://api.weixin.qq.com/cgi-bin/menu/create",
                [],
                json_encode($this->reduceMenu($menu))
            )
        );
    }

    /**
     * Deletes the current menu from the OA.
     * 
     */
    public function deleteMenu ()
    {
        $request = new Request('GET', "https://api.weixin.qq.com/cgi-bin/menu/delete");
        $this->client->send($request);
    }

    /**
     * Retrieve the current menu for the OA, and return it as an instance of `Garbetjie\WeChatClient\Menu\Menu`.
     *
     * @return Menu
     * 
     * @throws MenuException
     */
    public function getCurrentMenu ()
    {
        $request = new Request("GET", "https://api.weixin.qq.com/cgi-bin/menu/get");
        $response = $this->client->send($request);
        $json = json_decode((string)$response->getBody());

        if (! isset($json->menu->button)) {
            throw new MenuException('Unexpected menu JSON structure: `menu.button` not found', $response);
        }

        $menu = new Menu();

        foreach ($json->menu->button as $item) {
            $menu = $menu->withItem($this->inflateItem($item));
        }

        return $menu;
    }

    /**
     * Validates the supplied menu, and returns a boolean value indicating whether or not the menu *should* be a valid
     * menu according to the API.
     *
     * @param Menu $menu
     *
     * @return bool
     */
    public function validateMenu (Menu $menu)
    {
        foreach ($menu->getItems() as $item) {
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
        if (count($item->getChildren()) > 0) {
            foreach ($item->getChildren() as $childItem) {
                if (! $this->validateItem($childItem)) {
                    return false;
                }
            }

            // If there are children, the title cannot be longer than 16 characters.
            if (strlen($item->getTitle()) > 16) {
                return false;
            }
            
            return true;
        }

        // Title cannot be longer than 40 characters.
        if (strlen($item->getTitle()) > 40) {
            return false;
        }

        // URLs cannot be longer than 256 characters.
        if ($item->getType() == MenuItem::URL && strlen($item->getKey()) > 256) {
            return false;
        } elseif (strlen($item->getKey()) > 128) {
            return false;
        } elseif (strlen($item->getKey()) < 1) {
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

        foreach ($menu->getItems() as $menuItem) {
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
        $reduced = ['name' => $item->getTitle()];

        // Has children.
        if (count($item->getChildren()) > 0) {
            $reduced['sub_button'] = [];
            foreach ($item->getChildren() as $childItem) {
                $reduced['sub_button'][] = $this->reduceItem($childItem);
            }
        } // No children.
        else {
            $reduced['type'] = $item->getType();
            $reduced[$item->getType() === MenuItem::URL ? 'url' : 'key'] = $item->getKey();
        }

        return $reduced;
    }

    /**
     * Inflate the given array representation of an item, and return it as an instance of `Garbetjie\WeChatClient\Menu\MenuItem`.
     *
     * @param \stdClass $item
     *
     * @return MenuItem
     */
    protected function inflateItem ($item)
    {
        if (isset($item->sub_button) & count($item->sub_button) > 0) {
            $object = new MenuItem($item->name, MenuItem::KEYWORD);
            foreach ($item->sub_button as $subItem) {
                $object = $object->withItem($this->inflateItem($subItem));
            }
        } else {
            if (isset($item->url)) {
                $object = new MenuItem($item->name, $item->type, $item->url);
            } elseif (isset($item->key)) {
                $object = new MenuItem($item->name, $item->type, $item->key);
            } else {
                $object = new MenuItem($item->name, $item->type);
            }
        }

        return $object;
    }
}
