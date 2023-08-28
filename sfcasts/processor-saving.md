# Processor Saving

Coming soon...

In our state processor, we have successfully transformed the UserAPIObject into our
UserEntityObject. So let's save it! Saving things is pretty easy. We could inject the
EntityManager, persist and flush that entity, and be done with it. But I still want
to offload as much work as I can to the core DoctrineProcessor. And it turns out that
the DoctrineProcessor is actually a bit complex. It's called PersistProcessor, I'll
search everywhere. And for the most part, it kind of is doing things like persisting
and flushing. But it's actually got some pretty complex logic here for if you have a
put operation, which I'm not really using put operations. But that logic is there. So
this class is doing a bit more than just kind of persisting and flushing things. So
let's just use it instead of trying to roll our own logic. So the way we do this is
pretty familiar at this point. We are going to add a private processor interface,
PersistProcessor. And of course, to tell Symfony what service exactly we want, we'll
use the autowire attribute and pass service. And we can say PersistProcessor, the one
from... In this case, there's only one PersistProcessor, colon, colon, class. Very
nice. And then down here, we'll say this arrow PersistProcessor, arrow process, and
then entity, operation, and URI variables, and finally context. All the same
arguments are passed up here. Now, notice one thing. I kind of mentioned this
earlier. When it generated this class, it generated with a void return type. That's
not really true. You don't have to return anything from the state processors, but you
can. And whatever you do return, I'm actually going to return data, becomes
ultimately the object that is serialized and returned back to the user. If you don't
return it anything, it's going to use this already. So this is not really changing
anything, but I just wanted you to be aware of that. All right, so this should save
the database and it should serialize. It should work. Let's try it. Famous last
words. And it doesn't work. Still a 400 error. It's actually still the unable to
generate an IRI for the item. What's going on? Well, think about it. Our user API
object still doesn't have an ID. So we map it to a new user object. We save the new
user object. Doctrine gives that new user object an ID, but we never put it back on
to our user API object. So no worries. Easy fix. We'll say data arrow ID equals
entity arrow get ID. And now that has still fails, but this actually got further.
This is the you can see that the response did work. It's got a 201 status code, and
it returned with our new user information. It's failing on the part of the test where
it tries to then use the password to log in because we are currently setting the
password to a big to do. So we'll handle that in one minute. But first, I want to
mention that when we set the processor here on the top level API resource, this
becomes the processor for all the operations. So for a post, put, patch, and also
delete. Now, post put and patch are all the same, really, they just that just means
to save the object to the database. But delete is different, right? We need to remove
something from the database. So this is no problem. We're just going to make sure
that we actually handle that situation up here. So after we map our API object to the
entity, we need to figure out if maybe we need to actually delete that entity. To do
that, we can check if operations an instance of delete operation interface. And if it
is we want to delete that entity. Again, deleting isn't hard, but I'm going to
offload this to the core doctrine remove processor. So up here, I'll copy this
argument. Let's inject another processor. This one is going to be remove processor.
And I'll rename it to remove processor. Perfect. Then down here, we can say this
arrow remove processor, arrow process, and then pass entity operation, URI variables
and context like all the processors get. The kind of key thing here is we're going to
return no, in the case of a delete operation, we don't return anything at all. So we
return no from here, I don't have a test setup for that. But we'll just assume that
that works. And head in and tackle our last problem, which is hashing the plain
password. And we've done this before. So this is nothing too new. So the key thing
here is, we're going to check if the DTO has a password. And you know what, our DTO
actually doesn't have an action, I haven't added that to our DTO yet. Let's pop open
user API. There we go. And there's no password on here yet. So let's let's add one.
So I'm going to say public string password equals no, and put a comment on here. So
in the case of the API, this is always going to be the plain text password, we're
never going to be passing around a hash password on our API, that doesn't make any
sense. This is the plain text password when being set or changed. So back in our
processor, if DTO has a password, then we know we need to hash that and save it on
the user. And the reason it might not be there is, well, it must be there when we're
creating a user, right? Because all users must have a password. But if we were just
updating a user, and maybe just changing their username to something, then in that
case, there is no password. So there's nothing to hash, undo that. But if there is
one pass, that means that this is a new password that they're setting or changing.
And so now we'll hash that, of course, up here, we need to add one more argument,
private user password hasher interface, user password hasher. And down here, entity
arrow set password, which is the hashed password. And we'll set that to this error
user hash password arrow hash password, passing that the entity, the user object, and
then the plain password, which is going to be DTO arrow password. Phew. Let's try
that run the test again. And it fails. The annotation at the so this is me
accidentally having an extra at there. So I thought that was an annotation. Now let's
run the test. And it passes, which means it fully logged in using that password, it
works. But Oh, look at the dump JSON response. So this is after we post to create the
user. When it returns, it returns the password property and the plain text password
that the user just set. Whoops. Alright, so I want to make sure we understand this
fully. Our provider is used for all the read endpoints, all the get endpoints. It's
also used for the patch endpoint. And one of the key things you'll notice here is
that we are not setting a password, because we never because the plain password, the
passwords never something that we actually need to set onto our user API, it's not a
field that we want to return to the user API. So we're correctly not mapping it from
our entity to our DTO right here. So that's good. But when you do a patch, or a post
request, this is the one situation where the provider is never called. This data is
directly deserialized into our user API object that's passed to our processor. So
that means our DTO does have the plain password on it. And ultimately, it's that DTO
with the plain password that is returned to the user. And in any case, even with the
patch situation, the patch, we made a patch request, it would load our user API
object with no password, but then the password would be deserialized onto it. And
we'd end up with the same situation, we'd end up with a DTO object, user API object
that has a password, and that password is returned to the user. So this is a really
long way of saying that in our user API, this password is meant to be a write only
field, the user should never be able to read this field, only write it. Let's talk
about how we can do customizations like this inside of our user API class, while
avoiding the complexity of serialization groups next.
