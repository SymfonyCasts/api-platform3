# Entity Dto Provider

Coming soon...

So this entity class thing seems almost too good to be true. It gives us all the
flexibility, in theory, of a custom class. But we can reuse all the core doctrine,
provider, and processor stuff. But as we've seen, there are two catches, but both
fixable. First, there's the big catch that we are not allowed right now to have
custom property names that will cause an error when it tries to serialize. The second
catch is that write operations like post or patch don't work at all. Well, if we, for
example, posted a file to our endpoint, the data would be deserialized. But it
wouldn't be saved to the database. We can actually try this. We have a test set up
for this. Let's look here. User resource test, test post to create user. Over here,
we'll run it with symphony-php-bin-php-unit--filter-equals. And it returns a 400
error. If we open that up, we get this error, unable to generate an IRI for the item
of type user API. So what happens behind the scenes is that the serializer
deserializes this into a user API object. Yay. That user API object is then passed to
the core doctrine persist processor, the thing that normally saves entities to the
database. But because user API is not an entity, that processor does nothing. Then
when this user API is serialized back to JSON, the ID is still null because nothing
was ever saved to the database. And so therefore, the IRI can't be generated for it.
Now, we could fix this by creating a custom state processor for user API that saves
us the database. But even if we did, the right operations like post and patch just
aren't designed to work out of the box with this entity class solution. And the
reason is a bit technical, but really important. So internally, API platform has a
central object that it's working on. If we're fetching a single item, that central
object is that single item. So it has this idea of a central object. The central
object is really important. It's used, for example, in the security attribute. So
let's see here. We have an example. Perfect. So this is granted. This object here is
going to be that central object. So that makes sense. If we're making a patch
request, that means we're editing a dragon treasure. So the central object is going
to be a dragon treasure entity. And it's used in other places as well, this idea of a
central object. So here's the catch. When you're using the entity class solution with
a read operation, so one of these get requests, the central object will always be the
entity. So the user entity will be the central object. But with a write operation,
most importantly, the post operation to create a new user, that central object will
suddenly be a user API object. So there's some serious inconsistency where the
central object in some cases is the entity. In other cases, it's your DTO. And that's
going to make things like setting up those security systems difficult to do. It's
also at the heart of all of the problems that we're talking about. If we can make the
user API be the central object in all cases, then we'd have consistent security. And
it's also going to fix our problem of having the custom properties. How can we make
the user API the consistent central object? We can do that by writing a custom state
provider that returns the user API. So think about it right now, we know that the
state provider for our user API class is the doctrine collection provider. Well, when
the doctrine collection provider finishes its job, it returns a user entity, and then
that becomes the central object. So we're going to extend that state provider and
have it do the same thing, but return our DTO instead, this doesn't make complete
sense yet, that's okay, we're going to kind of put a couple puzzle pieces together.
And it's all going to work really nicely. So check this out, run bin console make
state provider. And I'm going to call this entity to DTO state provider, we're going
to create a generic state provider that's going to work for all of our cases where we
have API resource classes that represent an entity. So this isn't going to be
specific to users, we're going to keep this nice and generic. So there's our class.
We're going to use this later for Dragon Treasure. So now over a new user API, I'm
going to set provider to be entity to DTO state provider. So a second ago, this was
using the core doctrine provider, now it's using our provider. Now, of course, in
entity to DTO state provider, we could manually query for our user objects, our user
entity objects, turn those into user APIs and return them. But again, that's the
whole thing we're trying to avoid, we want to continue to reuse all that nice
doctrine query logic. That's the beauty of the state options thing. So to do that,
like we've done before, we're just going to decorate the core doctrine provider. So
public function underscore underscore construct. And I'll say private provider
interface collection provider. And to help Symphony know which one to pass in, we'll
do the auto wire attribute and say service. We'll say collection provider, make sure
we get the one from doctrine ORM, colon colon class. So we're passing in the core
collection provider. And then down here, I'm just gonna call that actually, let's say
entities equals this arrow collection provider arrow provide passing operation you
are variables and context. So we're just calling the internal one, and then I'm going
to DD that. So this is really no different so far then. Actually, all right, so let's
try our test. And 400 error, the same one as before, unable to generate an IRI. Oh,
nevermind, I'm using state provider, I shouldn't pass call the test at all. All
right, so let's flip over now. Let's refresh our users endpoint and got it. So we are
calling the core thing, it's giving back a back end paginator object. That shouldn't
be surprising. If we want to see what's inside of that paginator object, we can say
iterator to array entities, that'll kind of loop over that for us. And we can see we
get back five user entity objects. So at this point, our new provider isn't doing
anything special. We're still calling the core collection provider. And if we were to
return entities here, this would be exactly what we had a second ago, we'd have the
basically returning user entities just like our collection provider was doing. Our
goal is to return user API objects. And we're going to do that next.
