# Custom Field Collection Provider

Coming soon...

Ok, team, let's run all of our tests. These were all passing when I started the
tutorial, so let's check out the last response for the first thing. And we get, more
than one result was found for query, although one row or none was expected. And you
can kind of see, it's a little easier if you view the page source, you can see down
here this is coming from Doctrine and eventually from the item provider, that's the
core item provider we are calling, from our Dragon State provider. So it's not
totally obvious, but remember over here, the getCollection operation, which is what
this first test is actually testing for, it has its own collection provider which is
different than when you're trying to fetch a single item, which uses the item
provider. Unfortunately, when I had us set the provider here, that actually set the
provider for all of our operations. So it is possible to take a provider and just set
it on one specific operation like this. However, for simplicity, I actually do like
having a provider for all of my entire API resource. When that happens, you just need
to realize that this provider is going to be called both when it's fetching a single
item, and when it's fetching a collection of items. So right now what's happening is
this is being called when we fetch a collection of operations. But then because we're
calling the item provider, it's doing some weird stuff in Doctrine, and we're getting
that exception. And watch, check this out. Let's DD operation. And then come over
here, I'm going to copy that test name. Let's run just that test. And perfect. Check
this out. It is an instance of getCollection. So that's going to be the key thing.
This is going to be getCollection, getPatch, we're going to be able to use the type
of that operation to figure out what we need to do. Now the only operation that uses
the collection provider is the getCollection. So we can do in here is just call is
inject the collection provider and use it in just that situation. So check this out
up here. I'm going to copy the first argument, duplicate it, and here we're going to
use collection provider, the one from Doctrine ORM, and we'll call this collection
provider. Perfect now down here, here's the cool thing. We can say if operation is an
instance of, we could use getCollection here, but there's actually an interface that
it implements which is called collection provider interface. Collection operation
interface. Then we can return this arrow collection provider and then pass it
operation, URI variables, and context. Actually, I forgot the word provide on there.
There we go. Cool. So let's try that. Spin over or run our test again, and it still
explodes. Something down here about expected null to be the same as five. So let's
click this out and see if this helps us, and ah, look, it's an error. The actual
response we're getting says you must call setIsOwnedByAuthenticatedUser before
isOwnedAuthenticatedUser. So remember, that's that new property that we have inside
of our Dragon Treasure. That is, which for the item operation, we are setting down
here, and of course, for the collection operation, we need to do the same thing. We
kind of need to loop over each result and make sure we follow that same logic. So
first, I actually want to look at what this return value is, so I'm going to copy
that and DD that entire expression and run the test again, because you might think
it's going to be an array of Dragon Treasure objects, but it's actually a paginator
object, and this is important. This is actually what powers the ability for our API
resources to have pagination. It's not super important right now, but it is going to
be important later. We're going to dive more into pagination when we create a custom
resource. But for us, it doesn't matter much, because this object, if we loop over
it, we'll get the individual Dragon Treasure results. So check this out. I'm going to
delete this, and instead of the return, I'll say paginator equals, and I'm going to
help my editor up here by saying that this is an iterable of Dragon Treasure. Then
down here, we can foreach paginator as treasure, and then I'm actually just going to
steal this code from down here, paste it up there. So we're modifying that, and then
we can return paginator, and now when we run it, okay, better. It failed, but at the
very, very end, Dragon Treasure resource test line 37, let's go check that out. So
all the way up here, so you can see in this test, we create some treasures, we make a
get request to the collection endpoint, we verify some things, and then down here in
the bottom, I find the first member of this, and I basically check to make sure it
has the right properties. What's happening now is that the is mine property is there,
but we weren't expecting it. This is actually my bad. In the previous tutorial, when
we added the is mine property, that property would only even show up if it was true.
If a Dragon Treasure didn't belong to me, the property, the field wasn't even there
at all. It probably should have been. It's actually a little more proper that it is
always there. So in this case, I'm going to update the test, and it's green. All
right, let's run all of our tests again. Okay, down to just one failure. Test post to
create treasure, which not surprisingly, is a test where we post down here to create
a new treasure, and then verify that it was created. So it's failing right now with a
500 error, and if we open it up, it's that same error. You must call set is owned by
authenticated user. So what's going on here? If you think about it, our provider, if
it's a collection provider, we set that value. If it's an item, we set that value.
Well, in this case in the test, we're making a post request, and the post request is
unique because it is the only operation that does not have a provider. You can see
put and also patch has a provider, get and get collection of providers. Delete
doesn't show a provider, but it does. It actually has to use the provider to load the
item first, but post does not have a provider. The JSON we send is directly
deserialized into the treasure entity, and then it's saved. So there's nothing that
goes to the provider, and that means when it goes to JSON, that treasure still
doesn't have that property set. So the workaround here is that in the state processor
for the Dragon Treasure, right before or after saving it, that's when we need to run
this same logic of setting this property. So I'm actually going to copy that, go from
here, and we actually do have a state processor already for Dragon Treasure. It's
meant to set the owner. If it wasn't set, we're going to kind of hijack that. And
right after it saves, I'll paste that, but actually this state processor is called
for all. The way it's set up right now is it's called for every single API resource
we're decorating. So we need to have that same if statement from up here. So if data
is an instance of Dragon Treasure, then we will call our data, data arrow set is on
by user, and I'll update a couple of variables. I could have done this before the
inner processor that actually saves it. It doesn't really matter. But now it's going
to save it, it's going to populate this property, and then that's what's going to be
serialized in the JSON response of our post request. And now all the tests pass. All
right, so obviously we know that we can run code before or after an item saves by
having a custom state processor. But what if we need to run code only when something
specific changes? Like when a Dragon Treasure changes from unpublished to published.
Let's find out how next.
