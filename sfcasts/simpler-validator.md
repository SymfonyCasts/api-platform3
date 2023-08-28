# Simpler Validator

Coming soon...

We are down to one last failing test. Apparently we can steal treasures. And you do
this by having a treasure owned by one user and then patching a different user and
sending that treasure on this property. This should give us a 422 status code. It's
currently giving us a 200 status code. But that's actually okay. We fixed this
already in the previous tutorial. We talked about all the complexity of this. We just
need to reactivate and adapt that validator. So in the User API class, above the
Dragon Treasures property we can say TreasuresAllowedOwnerChange. And the logic
behind this is TreasuresAllowedOwnerChangeValidator. So previously we put this above
that same Dragon Treasures property inside of our user entity. It would loop over
each Dragon Treasure, use Doctrine's unit of work to get the original owner ID, and
then check to see if the new owner ID was different than the original owner ID. And
if it was, it would build a violation. Now first things first, this is not going to
be above a Doctrine collection field anymore. This is just going to be an array. And
let's just start by DDing value. And one other thing here, just to help make things
very clear, I'm going to put a little dump up at the top of the test that says
RealOwnerIs, and then I'll say OtherUserArrowGetID. That'll help us kind of track
whether or not it's getting stolen. All right, let's run just this test. Okay,
perfect. So the RealOwner is supposed to be 2. And apparently when we dump our array,
we can see the one Dragon Treasure object inside of there. And check this out. The
owner is still 2. So by the time we get into this validator here, we're past the
array of Dragon Treasure API objects there, and it looks like the owner is just fine.
The owner has not changed to another thing. But of course everything is fine. So far
in the test, all we've done is tell the serializer to load this specific treasure,
basically from the database, and of course it will be owned by this user, and then
set it onto our user API. So this object hasn't actually changed owners yet. The
problem is going to come later when we allow our state property to be changed. And
really our user API to entity mapper to map the new Dragon Treasures that are on that
user onto our user entity. This is the spot where, without going too deeply into it,
this would actually cause the owner of the Dragon Treasure to be changed. I know it's
kind of hard to think about. We talked more about this in the previous tutorial. It
has to do with the fact that, for example, when addDragonTreasure is finally called
on user, it's actually going to call setOwner in the treasure and change it then. So
the point is, the problem of the stealing is going to come later. We need to stop it
before saving in this validator. But right now in this validator, everything seems to
be fine. Watch, I'll prove that this is actually not going to work. I'm going to
temporarily short circuit this validator by putting a return statement there. And in
userResourceTest, we're just going to fetch API slash users slash other user arrow
getID. And then I'm going to put dump on there. And when we run the test, check this
out. Dragon Treasures is now on the user entity. It's empty for that user. It
shouldn't be empty. This other user should own this Dragon Treasure, but I'm showing
you that it actually was ultimately stolen right here. All right, to sort this whole
mess out in our validator, we need to know two things. First, we need to know what
the original owner for each of these Dragon Treasures was. And we just saw a second
ago that each of these Dragon Treasures API objects here still has their original
owner set on, so that's easy to get. The second thing we need to know is which user
we are trying to change these treasures to. And we don't have that info yet. To get
that, we actually need to change the target of the validator from this specific
property, where all we have access to are the Dragon Treasure objects, up onto the
class. That'll give us access to this user API object as well as the Dragon
Treasures. So check this out. I'm going to move this up above the class. Perfect. But
then to make that work, we actually need to open up that class. And I'm actually
going to get rid of the annotation stuff. We're not using annotations anymore. The
important thing here is to change this from attribute target or method to target
class. For some reason, my editor adds an extra slash there. We also need to override
a method here from the parent thing. Not sure why we had to specify the target in
both places, but we do. And here we're going to return self class constant. So this
is another way in the validator system that it figures out that this validator can be
applied to a class. And I'm going to add a little return type there that's optional,
but I might get a deprecation notice if I don't do that. All right. Now in our
validator, let's DD value. And all right. Perfect. So what we're going to see here is
it's dumping the entire user API object with ID one, right? Good stuff. Then the
Dragon Treasures property holds just our one Dragon Treasure, but there inside of
here, you can see what their original owner was. So this makes us really dangerous.
We can just check to see if the kind of new owner is different than the original
owner. If we do, we have a problem. This means actually life is a lot easier inside
of here. So I'm going to add an assert here that value is an instance of user API.
And then we're going to for each over value arrow, arrow Dragon Treasures as let's
call it Dragon Treasure API. And I don't need any of this unit of work stuff anymore.
We can actually fill this stuff in, we can say the original owner ID is going to be
the Dragon Treasure API arrow owner arrow ID. We saw already that that's still set to
the original ID. And then the new owner ID is going to be value arrow ID. That's it.
And if you want, I can code defensively in case for some reason there isn't an owner
there should be but depends on how your API is set up. Then this logic down here is
still perfect. If we don't have original owner ID or the original owner ID equals the
new owner ID, life is good. Else build this violation down there. So the cool thing
is unit of work, gone. You statements up here gone entity manager constructor thing
gone. This now becomes a really boring looking custom validation constraint. That's
thanks to the new system having our API class separate from our entity classes. So
let's run that test and green. Alright team, I think we got it. Let's remove this
dump from up top. And we're going to celebrate here by running the whole test suite.
Symphony PHP bin slash PHP units. And done all green, we have completely rebuilt our
system using DTOs. All right, friends, I took a little bit of work to kind of get
that all set up. But that's the whole point of DTOs. There's more groundwork in the
beginning for more flexibility and clarity later, especially if you're building a
really big robust API that you need to not change. Yeah, and bye. See you later.
