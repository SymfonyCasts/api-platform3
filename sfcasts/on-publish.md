# On Publish

Coming soon...

Okay, first, before we talk about isPublished, one thing I do want to mention is that
right now my processor, my process method is a void method, doesn't return anything.
That kind of makes sense, right? It passes us the data and our job is just to do
something with that data, not like return anything. However, technically the process
method can return something, and for consistency you probably should. So I'm going to
remove that type hint, that return type, and at the bottom I'm going to return the
data. And I'm going to do the other thing over in the user password state processor
just for consistency, and then return that. Again, it doesn't really matter, but
technically if you return something, this is actually the data that is going to be
serialized and returned to the user. If you don't return anything, it will just use
data. So really, by returning the same thing we're passed, we're not making any
difference. But this is actually going to... But it's kind of an interesting thing to
point out. If you return... This probably won't work, but if you return foobar,
technically from a processor, that's actually going to be the value that is
eventually returned to the user. So there might be some edge cases where that might
be useful for you, though. Usually you're just going to return the same data that
you're passed in. All right. Anyways, back to the thing at hand. Inside the state
processor, right after we save, we need to detect if the is published field changed
from false to true so we can run some custom code. The problem is by the time we get
to the state processor, the JSON from the user has already been used and updated the
object. So this data is going to have is published true. It's already been updated to
the latest data. Now in the last tutorial, we actually had a similar situation with
this where we had a validator and we needed to check to see if the owners of those
treasures changed to see if they were kind of being stolen. This is in a file called
treasure allow to change validator. So you can see what we actually did here is we
started with our value, which is actually our user object, which is a collection of
dragon treasures. Then we looped over them and we used the doctrine unit of work to
see what that treasure looked like at the moment it came from the database. Because
at this point, the dragon treasure had already been updated, but we were able to use
the unit of work to see what the owner ID looked like at the beginning so that we
could then see if the new owner ID is different than the original owner ID from the
database and we can figure that out. So unit of work is kind of your Swiss army knife
for going back to and seeing what something looked like at the start of the request
when it came from originally from the database before anything was updated. So do we
need to do the same thing here to use the unit of work to see what the dragon
treasure is published property looked at when we originally queried the database? The
answer fortunately is no. Because as I also mentioned back on that chapter, API
platform has a concept of original data. So right when the request starts, it clones
the top level object. So what I mean is since we're editing a treasure, it actually
fetches this treasure from the database using our state provider, and then it clones
it and kind of keeps that original clone hanging around so we can use it. So why
couldn't we use that original data before? Well, it's kind of subtle. But when it
clones the top, in this case, the top level data was the user object. And when it
clones the user object, it's a shallow clone. When you clone in PHP, it clones all of
the kind of scalar properties. But when it goes and tries to clone the individual
dragon treasures attached to a user, it doesn't clone those, it actually just gets
the reference, the new clone is attached to the original dragon treasures. So in this
case, had we used the original data would have given us the original user object
still attached to the same dragon treasures, which were updated. Boy, that's hard to
explain. But in this case, the is published property is just a scalar property. So if
we can get the original data that should have the correct is published property. How
do we get that? Well, notice we're past a very nice argument called context, which is
just useful information about what's going on. So right on top here, I'm going to DD
context. And let's run the test that we're working on right now. So I'll copy that
name again. And we will run that test. And okay, let's look inside of here. So
there's a whole bunch of good stuff in here. There's the operation that's being
currently executed. And let's see what else I area previous underscore data and check
out the is published. It's false. That's what it originally looked like. That's what
we need. Alright, so check this out. Let's get rid of that DD context. I'll go down
to the bottom, say previous data equals context, previous underscore data. And for
some reason, that's not there, we'll say no, that would happen in a POST request,
there is no previous data. And then if previous data is an instance of drag and
treasure. And data. So the current data, the new day is published. And the newest
published does not equal the original is published. I guess I can also just check to
see if this was previous was not published, but that's fine. Then we are being
published. Perfect. So the only thing you need to watch out for here is if this is a
POST request, there is no previous data. So if you wanted to kind of if you allow
treasures to be posted, published immediately when they're created, you'd also need
to check for that basically check to see if there is no previous data, but the data
is published, then you then you know. So let's try this out. We'll run our test
again. And we hit the published. Alright, so what are we going to do and publish?
Well, one of the entities I have in our project we're not using yet is just a little
notification entity, kind of have some message about something that's happened with a
specific treasure. So in our test, let's look for that. So at the end of the test,
we'll say notification factory, that's a foundry factor that I've already created,
colon, colon repository to get this little repository helper thing, then we can say
assert, and then count one. So remember, our database always starts empty in a test.
So we're going to check to make sure we have one row in there. Let's get rid of our
DD. And since we haven't done that yet, this should fail. And it does. Perfect. All
right. Now, this is the class I want, processor, let's get to work. So to save this,
the notification, we're going to need the entity manager. So I'll say private entity
manager interface entity manager. And down here, very simply, I'll put some boring
code notification equals new notification. And then I'll set a couple of properties
on that that are required. Like the related dragon treasure and a message. And
finally, at the end, we know the drill of this area and energy manager arrow persist
notification, and this arrow entity manager, arrow flush. That's it. Strat out. Boom,
just that easy. So now we're hearing whatever custom code we need when our item is
published. Next, let's create our first API resource class that is totally custom. So
not an entity at all.
