# Validation

Coming soon...

There are a bunch of different ways for the users of our API to mess things up like
bad J s O or bad data like passing a negative number for the value field. So let's
check into how are APIs working with some of those right now. So I'm gonna hit try it
out on the post endpoint and let's actually add invalid. Let's send some bad js o,
I'm gonna remove that comma right there. Execute and awesome 400 errors. So that's
what we want. A four. Any error that starts with four means that the client, the user
of her API made a mistake. So 400 means bad request. And you can see down here the
type is hydroco error and it's got an error occurred and syntex error. And this trace
only shows in the debug environment. So this wouldn't be shown on production. So
that's pretty awesome. That's already handled out of the box. So let's try something
different. Let's try sending empty json like we forgot to send any of our fields.
That is a 500 air not so great internally API platform creates our dragon treasure
object but doesn't set any data on it and it sort of explodes when it hits the
database because some of the columns are nu

And of course we expected this. What we're missing is validation and adding
validation to our API is like adding validation anywhere in Symphony. It's really
simple. So for example, we find the property name, we want name to be required. So
I'm gonna add the not blank say not blank and hit tab to add that you statement. And
you know what? Let's also do a oh and that's actually fine, but I'm actually gonna go
find that not blank up here. And change this to as assert. This is typically how you
see things done inside Symphony. And I'll say assert slash not blank. And then my
below, let's add one more. I'm gonna say length and we'll say that the name should be
at least two characters along a max, uh, 50 characters. And here's the max message.
Describe your loot in 50 chars or less. Cool. So let's try now I'll take that same
empty json, hit execute and awesome A 4 22 response, which which is a really common
response code to basically mean validation error. And check out this at type, it's a
constraint violation list. This is a special J S O N L D format. You might not have
remembered it, but earlier we actually saw this documented in the JSUN LD
documentation. So I'm gonna go to that slash api slash do

Slash api slash docs dot jsun LD and you to search for a constraint violation. There
it is. So there's actually built in classes like constraint violation and constraint
violation list built into our A P I along with our treasury resource. And you can
actually see what the structure of this is. A constraint violation list is just a,
basically a collection of constrainted violations and then it describes what the
constraint violation properties are. And we can see those over here. It's pretty
awesome. And there's a violations of property and it shows the property path and then
it's got the message below. All right, so let's add a few more things here. So we'll,
we'll add above the description property. We'll add not blank and above the value
we'll add greater than or equal to zero. So it's gotta be, can't be negative. And
then finally, cool factor we use greater than or equal to zero. And then we'll
actually add a second one of those. Change it to less than or equal to 10. So
something between zero and 10. And while we're here, we don't need to do this, but
I'm gonna initialize the value to zero and the cool factor to zero. So if it wasn't
set, we can just, it makes those fields not required in the api. That'll default to
zero if they're not set.

Now I'm gonna go back and try that same endpoint, look at that beautiful validation
and we can even trigger a little bit more by maybe adding a cool factor of 11. Yeah,
our system definitely does not like that. All right, so there's one kind of last way
that you can fail validation. That's by passing the wrong type. So the cool factor 11
will fail our validation rules, but what if we actually pass a string to that
instead? One we had execute, okay, 400 status codes. That's good. It fails with a 400
level status code. It's not a validation error, it has a different uh, type, but it
does tell the user what happened. The type of cool factor must be an INT string
given. So the point is invalid. Jason is taken care of. Bad types are taken care of

In bad types are taken care of because on the set cool factor, the system actually
sees this INT type here. And so it rejects it with this error right there. So the
only thing that we need to worry about in our application is a writing good code that
properly uses type pins and B, adding our validation. Adding validation for our
business rules, like the value should be greater than zero or the description is
required. API platforms then going to take care of the rest. Alright, next, our API
only has one resource right now it's our dragon treasure. Let's add a second
resource, a user resource so that we can link which user owns which treasure in the
api.

