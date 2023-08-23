# Custom Field Provider

Coming soon...

API Platform 3 introduced this really awesome concept of State Providers and State
Processors, which I've talked about a few times and we're going to dive a lot more in
this tutorial. API Platform has, strangely enough, on their upgrade guide a nice
section called Providers and Processors that I just find really helpful. So every API
resource class you have, whether it happens to be an entity or just a normal class,
is going to have a State Provider, which helps load the data, like load it from the
database, and a State Processor, which helps save the data later. The key thing is
that when your API resource is an entity, you get to use a bunch of built-in State
Providers and State Processors. So, for example, the git collection operation when
your API resource is an entity is automatically this collection provider, which is
going to query the database. Same thing with the git single one. It's a different
item provider that queries the database just for one of those items. And similarly,
there are processors like PersistProcessor, which, no surprise, persists your data to
the database. So as an example of this, in Episode 2, we decorated the State
Processor for the user entity so that we could hash the password. So here we're
hashing the plain password, and then we're calling the core PersistProcessor so that
it can still save it to the database. Now, speaking of this topic, the reason this is
going to be important is because another thing that we did in Episode 2 is we talked
about some ways to add totally custom fields to your API resources. And one way to do
that is actually by extending the normalizer. So we added this
addOwnerGroupsNormalizer, which adds some groups, but what I really want to focus on
here is that we were basically decorating the normalizer, and then if the object was
a Dragon Treasure, so if a Dragon Treasure was being turned into JSON, and the
currently authenticated user was equal to the owner of that Dragon Treasure, then we
added a totally custom isMine field. And we can see this, actually, in our tests,
testFunctionalDragonTreasureResourceTest. Let's see if I search here for isMine. Yep,
testOwner can see isPublished and isMine fields. So see here, we make a... In this
case, it doesn't really matter, but we make a pass request to the treasure, and then
when that treasure is being serialized, we check to see that we can see an isMine in
the response. So the important thing here is that the Dragon Treasure entity does not
have an isMine field. That was a way for us to totally add that custom field. The
downside of this is that if we look over in our documentation, there is no isMine
field. If I actually make a GET request, any of the treasures that I own will have an
isMine field, but you're not going to see isMine mentioned anywhere inside of here
because we just kind of snuck it in to the process via that custom normalizer. So
when you need to add a custom field to your response that doesn't exist in your
entity, there are really two better ways to do it. The first is by extending your
state provider, which we're going to do in a second. Another way is via a totally
custom API resource class, which, as I keep saying, is something we're going to talk
about later in the tutorial. And really, the more your entity and your JSON response
start to look different from each other, the more it makes sense to have just a
custom class instead of making your entity also an API resource like we've done so
far. Anyways, but if they look just a little bit different, then we can kind of do
some tricks and add a couple custom fields. So the point is we are going to add this
isMine field in a way where it actually shows up in our documentation. So step one is
I'm going to remove that from here. Just put a return statement now. Perfect. And
then over here, let's copy this test name and run it with symphony.php bin.php unit.
So we're running the bin.php unit file. We're running it kind of through the symphony
binary. As a reminder, this is so that it can kind of inject the database environment
variables in there. Then I'm going to do dash dash filter equals and paste that test
name so we just run that one. And perfect. You can see that down here expected null
to be the same as true coming from line 215 so it doesn't see our isMine field. All
right, so the way that you add this kind of proper custom field is we add it to our
entity but as a non-persisted property. So it doesn't really matter where but I'll go
right above the constructor. Let's add a private bool isOwnedByAuthenticatedUser.
There we go. I'm even going to put a little thing and say this is a non-persisted
property to help determine if this treasure is owned by the currently authenticated
user. And then I'll skip all the way down to the bottom and we'll add a getter and
setter for this. So it's not that common to have a method in your entity, a property
in your entity that doesn't save the database but it's a totally legal thing to do.
And since this new property doesn't have a default value, we're going to set this
somewhere else just to code a little defensively down here. I'm going to say if not
isSetThisArrowIsOwnedByAuthenticatedUser then we're going to throw a really clear
exception basically to remind us that we didn't set this yet. Perfect. And then
finally we're just going to expose this property to our API. So we can do that by
putting it in our group called treasure colon read. And then we're also going to
control the serialized name. Remember we wanted this to be called isMine. So remember
the serialization group, if I copy that, the serialization group comes all the way up
from up here. We've set our normalization context to treasure colon read. Actually, a
better way to look at it is down here. So treasure colon read. So those are all the
properties and methods that are going to be included in the JSON. All right, so if we
go and run the test now, it's going to fail the 500 error. As a reminder, we're using
browser so we can just kind of open this up, the error up in our browser really
easily. And it says you must call setIsOwnedByAuthenticatedUser So it's hitting our
exception, and that makes sense. We have created this property.

maker command for us, binconsole make state provider DragonTreasureStateProvider Cool. Let's spin over We'll go into this source currently DragonTreasureStateProvider You'll see it implement with a simple provider interface with a provide method So our job here is to return the data that would be using We would load the dragon treasure from the database and return it Before we think about doing that, let's just DD operation so we can see if this is executed. The answer if you're in test right now, is that this is not going to be executed automatically. If we look at that error here, we get the same thing before. It's not hitting our DD. So simply by creating a state provider and implementing provider interface, that's not magically used anywhere inside of your system. This is actually kind of cool. We are in control on our API resource class of which provider we want to use. So if we're in dragon treasure way up on top inside of our API resource, doesn't matter where, but I'll go after pagination items per page, add provider and the service ID or in our case, the class name to our provider dragon treasure state provider colon colon class. So now whatever needs to query the database for dragon treasure, it's going to use our state provider. That's exactly what's happening inside of the test. We're making a patch request. So the first thing it's gonna need to do is it's gonna ask the provider to load this treasure and then it's going to update the treasure with that Jason. So when we run the test now, perfect. We see it hits our DD statement and you can see it's dumping the operation, which happens to be this patch operation. Perfect. Now, in reality, we don't want to do the work of querying the database for the dragon treasure because we already know that there's a core provider that does that for us. We just want to have the core provider do its work. And then before we return the dragon treasure, we're going to set that new property before we return it. So to call the core provider, we're just going to basically decorate it. So we can say public function underscore underscore construct. Actually, let me keep that DD operation down there for now. And then we'll say private provider interface. And we'll call it item provider. So as a reminder. And when you're working with the patch or put operation, it uses an item provider, which is the one that knows to query for a single item in the database. So that's why I kind of call this item provider. And this is really what we want to have injected in there. Now, if we run the test now, it's going to fail, it's going to say cannot auto our service dragon state provider argument item provider references provider interface, but no such service exists. So a lot of times in symphony, if we just add it, if we just type in an interface, we're going to get the one service that we need. In the case of the provider interface, there's actually lots of providers. There's a item provider. There's a collection provider. There might even be other types of providers in the system. There's not just one provider. So when you decorate it, when you inject it like this, you need to tell a platform or tell symphony which provider you want. So we can do that with. A handy dandy auto wire attribute, so auto wire, let's say service colon. And the really cool thing about this is we can actually just use this class name item provider. So now we can say item provider. Make sure you get the one from ORM colon colon class. When we decorated the state provider in episode two, I give you kind of this long. There's also a long string service ID, but you can also just use the class name API platform sets up an alias for that. So now we run the test, we hit our DD again, which means it was able to inject this item provider. All right, so let's get to work here. Check this out. We can say treasure equals this arrow item provider arrow provide pass an operation you are variables and context. Whoops. In any case, this is a four or four. We can say if not treasure. Turn all. If not, treasure instance of drag and treasure will just return treasure. They'll probably just be no. And then for now, let's just set is owned authenticated user to true just to see if it works. And they'll return treasure. Run it and got it test passes. So we fetch the item from the database. We populate that field and then whatever you return here is eventually what is turned into the Jason. And of course, while we're here, I want to set this value for real. This is pretty easy. We're going to say private security. One from security bundle security. Let's make sure I have a comma after the first argument as well. And then it's a true. Will say this arrow. Security arrow get user if that equals. Treasure arrow get. Owner. Ann. Awesome test still pass. So this is a great example of decorating the provider, setting a non persistent field, and boom, we have a new custom field in our API and the field is documented. So if we open up this get operation here and scroll down, it knows you're going to get an is mine property back. However, we did just break our get collection operation. If we actually tried this endpoint, it would break. Let's find out why next and fix it.
