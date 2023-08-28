# Write Relation

Coming soon...

Open up our Dragon Treasure Resource Test. And I want to focus in on here on test
post to create treasure with login. We've already talked about making our endpoints
return relations, and we've set up the data mapper so that we can set up these
relationships. One thing we haven't talked about is being able to write to one of
these relationships. So right now when we use this post endpoint, we don't need to
send an owner field here. And the reason for that is because in our Dragon Treasure
API to Entity, we have some logic here that says, hey, if there is no owner sent on
the DTO, like here, there's no owner sent on this, this is DC relies into the DTO and
there's no owner, then we'll automatically set the owner and the Entity to the
currently authenticated user. But you are allowed to send the owner property and set
it to yourself. So let's actually try that. Slash API slash user slash user arrow get
ID. When we do this, it should hit this part of our test. So let's actually try that.
Symphony PHP bin slash PHP in it and run just that test and perfect. It hits and
check this out. It's a user API object. Now, this is actually really important. Let
me dump just the DTO so we can see things in more detail. All right, awesome. So most
of the time behind the scenes when we send this JSON data the serializer DC realizes
all of this into a Dragon Treasure API object and the name this string goes onto the
name property. This string goes on the description property and so on and we see that
over here string string a thousand and five dead simple, but something special
happens when the property when the field you're sending is a relationship field. This
IRI is actually transformed into the user API object. How does it do that? Well, the
answer is the state provider. So so far the only time that the state providers use as
far as we know is when you're actually fetching that resource. So if you fetch a user
here or here or you patch a user or you delete a user it's going to look up that user
or those users using the state provider, but there's one other spot where a state
provider is used and that's when you send an IRI on a relationship field in a post or
patch request. During the D serialization process. This IRI is taken. It's it sees
that it's for a user API object. It then calls the state provider to find that and
whatever the state provided returns is ultimately what is set onto the owner property
of our dragon treasure API. So it's just a cool thing to realize how that's working
behind the scenes. All right. Anyways in our mapper our job is pretty straightforward
here. We know that DTO owner is going to be a user API object. We ultimately need a
user entity. So we're going to go through that thing again where we use the mapping
system to go from user API over to user. So let's inject our micro mapper interface
micro mapper. Perfect and then down here and say entity arrow set owner this time.
We're going to use this arrow micro mapper arrow map to go from our DTO arrow owner.
To user colon colon class and remember whenever we map a relationship we probably
should put a max depth. So I'll say micro mapper interface colon colon max depth
equal arrow zero. We definitely only need zero here because what this is going to do
we know is actually just going to go query for a user object. We don't need to map it
to a deeper level. We would only need to do this if we were allowing something like
this if we are allowing and we're allowing this to be like an embedded object. Maybe
we were creating like a new one on the fly or even we're doing something crazy like
this where we are putting the add ID and then we're actually going to modify it.
These are things we talked about in previous tutorials. They are possible, but the if
we actually did try this API platform wouldn't allow this remember you can only write
embedded data to an object if you have the groups set up correctly the serialization
groups. We don't have that. So this is not something that is even going to be
allowed. So it's not something we need to worry about. We only need to worry about
this coming in and so we just need to make sure that we're loading the correct user
entity object here. All right. So now when you run that test it's good. So we now are
allowed to write owners. We understand a bit more about how the IRI becomes the DTO
object inside of the system. All right, next up. Let's turn to setting Dragon
Treasures onto users. That's a collection property being able to add or remove them.
This will require an extra trick.
