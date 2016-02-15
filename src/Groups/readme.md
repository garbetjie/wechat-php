# WeChat PHP SDK: Group management

WeChat users can belong to a single group at any given. By default, each user is part of a default group, named
"Ungrouped". New groups can be created on the fly, but there can be a maximum of 100 groups available in any OA.

## Using the `Group` module

All functionality provided in the `Group` module is available through the `WeChat\WeChat::groups()` method. An example
showing how to retrieve a group instance is shown below:

        $wechat = new WeChat\WeChat();
        $groupModule = $wechat->groups();
        
        // $groupsModule is now an instance of `WeChat\Groups\GroupsModule`.
        
## Available functionality

### Create a group

    try {
        $wechat = new WeChat\WeChat();
        $wechat->authenticate( 'APPID', 'APPSECRET' );
        $group = $wechat->groups()->create( 'Group name' );
        
        // $group is now an instance of WeChat\Groups\Group.
    } catch ( WeChat\Groups\Exception $e ) {
        // Could not create the group.
    }

### List all groups

    $groups = $wechat->groups()->all();
    /* @var WeChat\Groups\Group[] $groups */

### Update a group.

    try {
        $group = new Group( 100, 'Group Name' );
        $wechat->groups()->update( $group );
        
        // Group with ID 100 has been updated to match the provided group.
    } catch ( WeChat\Groups\Exception $e ) {
        // Could not update the group.
    }