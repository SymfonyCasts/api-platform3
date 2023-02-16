# Scopes

Coming soon...

Our API tokens all have an array of scopes, though we're not using them yet. The idea
is that when a token is created, you can select, you can select which per permissions
it has, like maybe a token gives, uh, the permission to create new treasures but not
edit existing treasures. So what the idea is, is that maybe we can map whatever
scopes A token has two roles in Symphony Snow can deny or allow access A little bit
more granular. Right now on our API token handler, we're just returning the user and
we're logging in fully as that user. And then that user is just getting whatever
rolls it has on its user object. But now I'm gonna use the scopes as the rolls. So
how can we do that? Well, and talk to me a little bit tricky. Our access token
security system, the actual code behind that I'll hit shift. Shift is called Access
token authenticator. So for example, this is actually the thing that grabs the token
off the request and actually calls our access token, get user badge from Access
token. The tricky thing here is that ultimately down here in this Create token, this
is actually where it figures out what roles the currently authenticated user should
have. And you can see no matter what it always calls, get U user arrow get roles, and
we don't really have a lot of control over this.

So we could create a custom authenticator so that we could implement our own create
token method and do it manually. But man, that's a lot of work just for that one
small detail. So instead I think we can kind of cheat. So check this out, end user.

Scroll

Up to the top where we have our properties and let's add a new property. It's gonna
be a private nullable array called Access Token Scopes. And we'll initialize it to
Null Notice this is not a persisted column on purpose. This is just a temporary
column that will temporarily store the scopes that this user should have. If we log
in via an access token, I'll show you how it's used. So down at the bottom I'm gonna
create another uh, public method called Mark as token authenticated with an array
scopes argument. This is something that we're gonna call during authentication inside
of it, we're just gonna say this arrow access token scopes equals scopes. Easy peasy.
Now here's the getting interesting. I'm gonna search for the get rolls method. So we
know that no matter what, this is what's gonna be called after user authenticates and
whatever roles they have in the database plus whatever, plus roll user, that's what
they're gonna have. So we need to kind of sneak our scopes into that a little bit.

Daddy.

So we're gonna rearrange this method a little bit. So first I'll say if that access
Token scopes property is no, that means we're logging in like a normal user. So we're
gonna do in that case is we'll set roll to this arrow roll, so we get all the roll
that are on the user. Then I'm also gonna add an extra roll here called Roll Full
User that we're gonna talk about in a minute. Now else if we did log in and be an
access token, we'll say rolls equals this arrow access token rolls, and we'll still
make sure that every roll has Roll U. We always have roll user. It's just a
convenient role just to check to see if you were logged in at all. All right, thanks
to this setup over in our API token handler. Right before we return the user badge,
we can say Token arrow, get owned by Arrow Mark token as authenticated, and then pass
it. Token arrow, get scopes. All right, let's try that back over here in API
platform. I'm already like log, I'm already logged my API token. I'm just gonna
re-execute this request down here you can see my authorization header. Then I'll
click to open the profiler for that request. Head down to security and Okay, good.

Perfect. Look, we are logged in as that user, but we have roll user and then roll
user edit and roll Treasure, create the two scopes from that token. But if we were to
actually log in through the full mechanism, instead of having these scopes, we would
have whatever roles the user normally has, plus this role full underscore user. Now
we're not actually using any of these roles to protect any endpoints yet. We're gonna
do that in a little while and we're gonna do that for example, by, you know,
protecting the post treasures endpoint, making sure we require this role. But we
wanna make sure that if a user is logged in via the normal method, that they still
are able to create a treasure even though they don't have this role. So that's where
this roll full user comes in handy. So I'm gonna open config packages, security that
Yammel, and anywhere inside of here I'm gonna create a roll hierarchy. And I'm also
gonna spell that word correctly. And we're gonna say here is roll full user. If
you're logged in as a full user role, we're actually gonna give you all of the
possible scopes that a token has. So I'm gonna copy these three tokens here. So roll
user, edit, roll, treasure, create

And roll Treasure edit. Yeah, it's ki we do have to be careful to make sure that if
we add more scopes here, we add them over here. So, man, if we protect something in
our system, we require rule user edit. If a user's logged in as the full user, they
will get this automatically. Thanks to roll hierarchy. All right, team, we're not
gonna try this yet, but we'll see it in a few minutes. All right, Tim, we are done
with authentication. Woo. So next, let's start talking. Let's start into
authorization by learning how to lock down certain operations so that only certain
users can access them.

