# WeChat PHP client library

## User management

This library provides a simple way to manipulate users that are following your official account.

### Using this library

All functionality provided in the `Users` service is available through the `WeChat\WeChat::users()` method.
 An example showing how to retrieve a group instance is shown below:

        $wechat = new WeChat\WeChat();
        $usersService = $wechat->users();
        
        // Alternatively:
        $usersService = new WeChat\Users\Service(new WeChat\Client());
    
There is also the bulk service that is available, which allows you to manipulate and retrieve multiple users in a single
 operation. The usage is very similar to the single-user service:

    $wechat = new WeChat\WeChat();
    $bulkUsersService = $wechat->users()->bulk();
    
Exceptions that are thrown are generally an instance of `WeChat\Users\Exception`.
        
### Single-user functionality

#### Change a user's group

    $wechat->users()->changeGroup('user_open_id', 1);
    
#### Retrieve the current group ID of a user

    $groupID = $wechat->users()->group('user_open_id');

#### Retrieve user's details

    $user = $wechat->users()->get('user_open_id');
    
#### Count the total number of followers for the official account

    $count = $wechat->users()->count();

#### Paginate through the list of users

    $users = $wechat->users()->paginate('next_token', 10);
    
When paginating through users on an official account, only the OpenIDs of each user will be returned.
 If you want to retrieve the user data, you'll need to fetch them manually.

For example:

    $paginated = $wechat->users()->paginate(null, 1000);
    // $paginated['users'] will contain all the OpenID's of the found users.


### Bulk user functionality

#### Change user groups

Returns an array containing the OpenID's of users whose group was not changed.

    $failedOpenIDs = $wechat->users()->bulk()->changeGroup(['array', 'of', 'OpenIDs'], 1);

#### Retrieve user details

Retrieves the profiles of all the supplied OpenIDs, and retrieves them in parallel.

If a callback is supplied, every time a profile is retrieved (or is failed to be retrieved), the callback will be executed.
 The callback needs to function the following signature:
 `function (WeChat\Users\User $user = null, $id)`, where the first argument is the `User` object (or `NULL` if the retrieval
 failed), and the second argument is the ID of the user.

If no callback is supplied, the method will return an array containing all the `User` objects representing the users,
 indexed by their OpenID. If a user cannot be retrieved, the value for the OpenID will be `NULL`.

    // No callback supplied
    $profiles = $wechat->users()->bulk()->get(['array', 'of', 'OpenIDs']);
    /*
        $profiles = [
            'array' => new User(),
            'of' => new User(),
            'OpenIDs' => null,
        ];
    */
     
    // Callback supplied.
    $wechat->users()->bulk()->get(['array', 'of', 'OpenIDs'], function (WeChat\Users\User $user = null, $id) {
        if ($user === null) {
            echo "failed to retrieve user with id {$id}\n";
        } else {
            echo "successfully retrieved user with id {$id}\n";
        }
    });
