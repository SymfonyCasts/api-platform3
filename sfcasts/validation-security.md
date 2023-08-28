# Validation Security

Coming soon...

Let's talk about validation. So when we post to our endpoint, the internal object now
is going to be our UserAPIObject. And that's what is going to be validated. So for
example, if we send no fields to our POST request and run that test, it fails with a
500 error. And I bet you can guess why. Look, it says, set email argument 1 email
must be of type string coming from our state processor on line 59. So because there's
no validation constraints at all in our UserAPI, our email remains null. And then
over here on line 59, we try to transfer a null email onto our entity. It doesn't
like that. And even if it did like that, this would eventually fail in the database
because the email is not supposed to be empty. So we're missing validation. That's
easy enough. Easy enough to add. The point is validation happens now on your UserAPI
class. Before I get into validation specifically, let's actually kind of specify
operations. So we can have just the operations that we want. So we're going to want
the get single, the get collection, and also POST. Now, as part of POST, actually,
let me get back to that in a second. We also want a patch operation and a delete
operation. Now, originally on our user entity, when that was part of our API, our
POST operation had an extra validation context with groups set to default, and then
POST validation. So what this means is that when the POST operation happens, it's
going to run all the validators in the default group, which are all the normal
validators, plus any that are in this POST validation group. And I'm going to show
you where this comes into play in a moment. But we're actually just repeating code
from an earlier tutorial. This code used to live over on our user entity. All right,
down here, let's see. ID is not even writable. Email, we want that to be not blank.
And we also want it to be an email. We want username to be not blank. And then
password, this is the interesting one. Password actually should be allowed to be
blank when we are doing a patch request, but not on a POST request. So that's where
we'll say not blank, but we'll pass groups set to POST validation. So this will only
be run when we're validating the POST validation group, which means this will only be
run when we are doing the POST operation. So that's how that all works. And that
should be it. We're going to run the test now. Beautiful 422 status code. That's the
validation error. That's what we wanted. Now one thing I want to note here is that
back when we had this on the user entity, one of the other validation constraints we
had was a unique entity. That basically makes sure that we don't try to create two
users with the same email or two users with the same username. I don't currently have
that on my user API. I should have that. Unique entity only works on entities, so
you'd actually need to have a custom validation. You need to create a custom
validator to kind of add that logic for our user API. I'm not going to worry about
that right now, but I wanted to point that out. All right, so let's go back over here
and re-add our fields, fix that test again. So we have validation. The next thing we
need to re-add code that used to live on user is security. So up here on the API
level, so for the entire operation by default, we are requiring is granted role user.
So basically you need to be logged in in order to use any of the operations for this
resource by default. Then we overrode that first in the post because for the post,
you definitely can't be logged in yet because you're actually registering your user.
So here we can say security and we can set that to is granted public access to
special attribute that will always pass. And then down here for patch, we had a
little thing before that was security is granted role user edit. So in our API, we
kind of made it so that you could only modify a user if you had some special role
that allowed you to modify users. So again, you might set this up different in your
application. We're just repeating kind of what we set up in the previous tutorials
for our user entity. All right, so let's actually run all of our tests for our user.
So test functional user resource test. And oh, not bad, three out of four. So we just
have one failure. It's on a method called test treasures cannot be stolen. So if we
check that test out, this is a really interesting test where we patch to update a
user. And then we try to set the dragon treasure to the treasure of a different user.
You can see this dragon treasure here is owned by other user so we're currently
updating user. So what this effectively is trying to do is steal this dragon treasure
from other user and make it part of user. And you can see we are asserting that that
was 422 status code. So previously we had a custom validator. We actually still have
it. It's this treasures allowed owner change validator. But it's not being applied to
our user API and it needs to be updated to work with our user API. I just wanted to
mention that. That's something we're going to worry about later. Even more important
right now is that the dragon treasures property isn't even writable in our user API
above dragon treasures. We have that as writable false. So in a little bit, we're
going to change that so that we can write dragon treasures again. And when we do
that, we'll bring back that validator and make sure this test passes. But other than
this one test, all of our user stuff is now passing. So next up, if you look at our
processor or our provider that we created, these classes are almost generic. They
could almost work for user API and maybe a future dragon treasure API class. The only
code that is specific to user is the code that maps to and from the user entity and
the user API class. So the missing piece to make this generic is some sort of a
mapping system to do that conversion outside of this class. Let's add that.
