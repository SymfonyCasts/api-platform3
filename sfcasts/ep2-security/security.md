# Deny Access with The "security" Option

We've just talked a lot about authentication: that's the way we tell the API
*who* we are. Now we turn to authorization, which is all about denying access to 
certain operations and other things *based* on who you are.

## Using access_control

There are multiple ways to control access to something. The simplest is in
`config/packages/security.yaml`. Just like normal Symfony security, down here, we
have an `access_control` section. If you want to lock down a specific URL pattern by
a specific role, use `access_control`. You could use this, for example, to require
that the user has a role to use *anything* in your API by targeting URLs starting
with `/api`.

## Hello "security" Option

In a traditional web app, I *do* use `access_control` for several things. But most
of the time I put my authorization rules inside *controllers*. But... of course,
with API Platform, we don't *have* controllers. All we have are API resource
classes, like `DragonTreasure`. So instead of putting security rules in controllers,
we'll attach them to our *operations*.

For example, let's make the POST request to create a new `DragonTreasure` require
the user to be authenticated. Do that by adding a *very* handy `security` option.
Set that to a string and inside, say `is_granted()`, double quotes then
`ROLE_TREASURE_CREATE`.

We *could* simply use `ROLE_USER` here if we just wanted to make sure that the user
is logged in. But we have a cool system where, if you use an API token for
authentication, that token will have specific scopes. One possible scope is called
`SCOPE_TREASURE_CREATE`... which maps to `ROLE_TREASURE_CREATE`. So we look for
*that*. Also, in `security.yaml`, via `role_hierarchy`, if you log in via the
login form, you get `ROLE_FULL_USER`... and then you automatically also get
`ROLE_TREASURE_CREATE`.

In other words, by using `ROLE_TREASURE_CREATE`, access will be granted either
because you logged in via the login form *or* you authenticated using an API token
that has that scope.

Let's try it. Make sure you're logged out. I'll refresh. Yup, you can see on
the web debug toolbar that I'm *not* logged in... and Swagger does *not*
currently have an API token.

Let's test the POST endpoint. Try it out.. and... just Execute with the
example data. And... yes! A 401 status code with type `hydra:error`!

## More about the "security" Attribute

The `security` option actually holds an *expression* using Symfony's expression
language. And you can get pretty fancy with it. Though, we're going to try
to keep things simple. And later, we'll learn how to offload complex rules to voters.

Let's add a few more rules. `Put` and `Patch` are both edits. These are especially
interesting because, to use these, not only do we need to be logged in, we probably
need to be the *owner* of this `DragonTreasure`. We don't want *other* people to
edit *our* goodies.

We're going to worry about the ownership part later. But for now, let's at least
add `security` with `is_granted` then `ROLE_TREASURE_EDIT`. Once again, I'm using
the scope role. Copy that, and duplicate it down here for `Patch`.

Oh, and earlier, we removed the `Delete` operation. Let's add that back with
`security` set to look for `ROLE_ADMIN`. If we decided later to add a scope that
allowed API tokens to delete treasures, we could add that and change this to
`ROLE_TRESURE_DELETE`.

Let's make sure this works! Use the GET collection endpoint.
Try that out. This operation does *not* require authentication... so it works just
fine. And we have a treasure with ID 1. Close this up, open the PUT operation,
hit "Try it out", 1, "Execute" and... alright! We get a 401 here too!

## Adding "security" to an Entire Clas

So adding the `security` option to the individual operations is probably the most
common thing to do. But you can also add it to the `ApiResource` itself to apply
to the entire class. For example, on `User`, we probably want *every* operation
to require authentication... except for the `Post` to create, because that's
how you would register a new user.

So up here, add `security` and look for `ROLE_USER`... just to check that we're logged
in. And because this class has a sub resource... and this *also* allows us to fetch
a user, be sure to add `security` here too. Keep close track of security if you're
using subresources.

Ok, so now *every* operation on `User` requires you to be logged in. But... we
*don't* want that for the `Post` operation. To add flexibility, go up to the first 
`ApiResource`, add the `operations` option, and, real quick, list all the normal
operations, `new Get()`, `new GetCollection()`, `new Post()`, `new Put()`,
`new Patch()`, and `new Delete()`.

Now that we have those, we can customize them. For `Post`, since we want this
to *not* require authentication, say `security: 'is_granted()` passing a special
fake role called `PUBLIC_ACCESS`.

This will *override* the security rule that we're passing on the resource level. Oh,
and while we're here, for `Put`, set `security` to look for `ROLE_USER_EDIT` since
we have a scope role for editing users. Repeat that down here for `Patch`.

I love it! Refresh the whole page. We're most interested in the `POST` users
endpoint. We are *not* authenticated, so hit "Try it out" and I'll leave the
default data. "Execute" and... we nailed it! A 201 status. That *did* allow
anonymous access.

## Checking the Security Decisions

Oh, and super fun: if you ever want to see the security *decisions* that were made
during a request, open the profiler for that request, go down to the "Security"
section then "Access Decision". For this request, only one decision made by the
security system: it was for `PUBLIC_ACCESS`, and that *was* allowed.

Next: our API is getting complex... and it's only going to get *more* complex. It's
time to stop testing our endpoints manually via Swagger and start testing them with
automated tests.
