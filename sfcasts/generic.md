# Generic

Coming soon...

UserAPI is now a fully functional API resource class. We've got our Entity2DTO state
provider that calls the core state provider from Doctrine which gives us querying,
filtering, pagination, all that good stuff. Then down here we leverage the
Micromapper system to convert the UserEntity objects into our UserAPI DTO. And we do
the same thing with the processor. We use the Micromapper to go from our UserAPI to
our UserEntity. And then we call the core Doctrine state processor to actually do the
saving or deleting. I love that! At this point, my ultimate goal is to create a
Dragon Treasure API and repeat all this magic we just did. And if we can make these
processor and provider classes completely generic, that is going to be super easy. So
let's start in the provider. If you search for user, there's only one spot we're
using it. That's where we're telling the Micromapper what class to convert our
UserEntity into. So can we fetch this dynamically? Well up here, our provider has
passed the operation and also some context. Those are two really good pieces of
information. So I'm actually going to dump both of those. And since this is our
provider, we can just go refresh collection endpoint and boom! So you see we get our
getCollection operation. And one of the key things is that every operation stores the
class that it's attached to. So this is real simple here. We can say resource class
equals operation getClass. Now that we've got that, down here, we can make that an
argument. String resource class, and we'll pass that instead. Now we just need to put
that resource class as the argument when we call mapped entity to DTO there. And
right there. And just like that, once I get rid of this use statement, the word user
is mentioned nowhere in the provider, and it still works. All right, so let's do the
same thing for processor. If I search for user here, it's the same problem, except
this time we need the UserEntity class. We need to figure out that that's what we're
converting to. So up on top, I'm going to DD operation. And for this, I will run one
of my tests so I can hit the dump. And beautiful. Okay, so you can see here's our
post operation. And the class is, of course, the user API. But in this case, we want
to figure out that what we really need is that we're is the user class. The cool
thing here is that, remember, in our user API, we've specified the state options.
This state options is what says that our user API is tied to this UserEntity class.
And this is something we can read off of the operation. So let's see down here, I
believe. So let's see if we scroll down a little bit. There it is. Check it out.
There's state options property with this options object with the entity class on it.
It's a little bit deeper, but we can fetch that out. So check this out. So check this
out. In our processor, I'll go back up. Start by saying state options equals
operation arrow get state options. And then just to help my editor and also in case
we have any misconfiguration, I'm going to assert that state options is an instance
of options from doctrine ORM, you can technically put different option classes for
your state options. Our processor is meant to work in the cases where we're using the
doctrine ORM state options. So this is meant to work for this exact case right here.
Then below that, we can say entity class equals state options arrow get entity class.
We don't need this assert down here anyways. And now I can pass that entity class to
map DTO to entity and use that down here. So add a string entity class argument. Pass
that there. And now when I search for user, get rid of those two use statements, no
more instances of user inside of here. And our test passes. All right, we have a
totally reusable provider and processor system. So next, let's create a dragon
treasure API class and repeat this magic and see how quickly we can get things to
fall into place.
