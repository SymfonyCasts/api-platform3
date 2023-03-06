# API Token Scopes

Each `ApiToken` has an array of scopes, though we're not using that yet. The idea
is cool: when a token is created, you can select which permissions it has. Like maybe
a token gives the permission to create new treasures but not edit existing treasures.
To allow that, we're going to map whatever scopes a token has to *roles* in Symfony.

## How are Roles Loaded Now?

Right now in `ApiTokenHandler`, we're basically returning the user... and then the
system authenticates as fully as that user. This means we have whatever roles are
on that `User` object. So the question is: how could we *change* that so that we
authenticate as this user... but with a *different* set of roles: roles based on
the scope from the token?

We're using the `access_token` security system. Hit Shift + Shift and open a core
class called `AccessTokenAuthenticator`. This is cool: it's the actual *code* behind
that authentication system! For example, this is the code that grabs the token off
the request and calls *our* token handler's `getUserBadgeFrom()` method.

The *roles* the user will have are *also* determined in this class: down here inside
`createToken()`. The "token" is, sort of a "wrapper" around the `User` object in
the security system. And *this* is where we pass it the roles it should have. As
you can see, no matter what, the roles will be `$passport->getUser()->getRoles()`.
In other words, it *always* calls `getRoles()` on our `User` object... which just
returns the user's `roles` property.

## Setting up the Custom Roles System

So, there's no great hook point. We *could* create a *custom* authenticator class
and implement our *own* `createToken()`. But that's a bummer because we would need
to completely reimplement the logic in this class. So, instead we can... kind of
cheat.

Start in `User`. Scroll up to the top where we have our properties. Add a new one
: `private ?array` called `$accessTokenScopes` and initialize it to `null`.

Notice that this is *not* a persisted column. It's just a place to temporarily store
the scopes that this user should have. Next, down at the bottom add an new public
method called `markAsTokenAuthenticated()` with an `array $scopes` argument. We're
going to call this during authentication. Inside, say
`$this->accessTokenScopes = $scopes`.

Now here's where things get interesting. Search for the `getRoles()` method. We
know that, no matter what, Symfony will call this during authentication and whatever
this returns, that's the roles the user will have. *We're* going to "sneak in"
our scope roles.

First if the `$accessTokenScopes` property is `null`, that means we're logging in
as a *normal* user. In this case, set `$roles` to `$this->roles` so that we get *all*
the `$roles` on the `User`. Then add one extra role here called `ROLE_FULL_USER`.
We're going to talk about in a minute.

Else, if we *did* log in via an access token, say `$roles = $this->accessTokenRoles`.
And, in both cases, we still want to make sure that we *always* have `ROLE_USER`.

This this in place, head over to `ApiTokenHandler`. Right before we return the
`UserBadge`, say `$token->getOwnedBy()->markAsTokenAuthenticated()` and pass
`$token->getScopes()`.

Done! Let's take it for a test drive! Back over on Swagger, it already has our
API token... so we can just re-execute the request. cool: we see the `Authorization`
header. Click to open the profiler for that request... and head down to "Security".
Yes! Look: we're logged in as that user, but with `ROLE_USER`, `ROLE_USER_EDIT` and
`ROLE_TREASURE_CREATE`: the two scopes from the token. But if we were to log in
through the login form, instead of these scopes, we would have whatever roles the
user *normally* has, plus `ROLE_FULL_USER`.

## Giving Normal Users sudo Access with role_hierarchy

In a little while, we'll start using these roles to protect different API operations.
For example, to use the POST treasures endpoint, we'll require `ROLE_TREASURE_CREATE`.
But we *also* need to make sure that if a user is logs in via the login form, they
can still use this operation, even though they won't have this exact role. That
is where `ROLE_FULL_USER` comes in handy.

Open `config/packages/security.yaml` and, anywhere, add `role_hierarchy`... I
recommend spelling correctly. Add `ROLE_FULL_USER`. So, if you're logged in as a
full user, we going to give you all of the possible scopes that a token could have.
I'll copy these three scope roles: So `ROLE_USER_EDIT`, `ROLE_TREASURE_CREATE`
and `ROLE_TREASURE_EDIT`. We *do* need to be careful to make sure that if we add
more scopes, we add them here too.

Thanks to this, if we protect something in our system by requiring `ROLE_USER_EDIT`,
users that are logged in via the login form will have access.

Ok team, we are done with authentication! Next, let's start into "authorization"
by learning how to lock down certain operations so that only certain users can access
them.
