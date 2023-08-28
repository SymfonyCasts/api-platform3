# Dragon Treasure Dto

Coming soon...

All right, time to convert our Dragon Treasure API resource into a proper new DTO
class. So we're going to start, I love this, by just deleting tons of stuff. So I'm
going to delete everything related to the API platform on my Dragon Treasure. And
we're going to add this stuff little by little. It's just going to be easier if we
kind of clean house and get rid of it right now. So let's get rid of all the filter
stuff, let's see here, and even the validators, serialization group stuff, all that.
And let's walk through and do some cleanup on all of our properties. You can see it's
quite a bit of complex stuff here. We're not going to add all this stuff back, but
we're going to learn, we're going to add the most important stuff back and see how it
looks inside of our new system. So I'll scroll down, make sure I get everything. And
that should be it, cool. Now it's a good old fashioned, boring entity class. I'm also
going to remove a few other files here. So in API platform, I'm going to get rid of
the admin groups context builder. This was kind of a complex way to make groups
fields readable, writable by your admin. We're going to solve that with API property
security. I'm going to get rid of a custom normalizer we had, which added a custom
field and also an extra property group. And then I'm going to get rid of the custom
Dragon Treasure provider and processor. Anything custom we'll add in a different way.
Now notice one thing I did keep was the Dragon Treasure is published extension. As I
explained, because the new system is still going to use the core doctrine collection
provider, this query extension stuff is still going to work and is still going to be
called automatically. So that's cool. It's one less thing that we need to worry
about. All right, so if we go over and refresh the documentation right now, we have
just quest and user. Though you'll notice some Dragon Treasure stuff down here. This
is actually because right now, our user API is still referencing the Dragon Treasure
entity. So it's trying to, even though Dragon Treasure isn't an API resource, it's
kind of trying to document it because it's going to be included in the API right now.
We're going to fix that and completely use our API classes everywhere. We'll see
that. All right, so in API resource, let's create a new class, Dragon Treasure API.
And I'm going to go and steal kind of the basic stuff off of our API resource on user
API. I'll paste that. And actually, I'm going to leave, I'm going to delete the
operations for now. I'll delete those use statements. I just want to get just the
littlest I can do. So I will use a short name of treasure, we'll say items for page
10, leave security off. And the most important thing is that we have provider,
processor, the two that we've created, and state options pointed to Dragon Treasure
colon colon class. Beautiful. And then to start, I'm also going to grab that
identifier code, we are going to need that int ID. But like before, we don't actually
want that to be part of our API. So it's readable, false, writable, false. And just
to start, I'm going to add a string, a name property only. And that's it. So this one
tiny class, let's go try it, see what happens. First if I refresh the API, beautiful,
so our treasure endpoint is there. Let's try the collection endpoint. And we get no
mapper found from the dragon treasure entity to dragon treasure API. So this is a
great thing. The only real work that we need to do is implement those mappers. So
let's do it to open this mapper directory. And we'll create a class called dragon
treasure entity to API mapper. And we've done this before. So we're gonna implement
mapper interface, and then add the as mapper attribute, we're gonna go from dragon
treasure colon colon class to dragon treasure API colon colon class. So now the
migramapper should use this, I'll generate the two methods that it needs, load and
populate. And then I'll kind of start that same way for clarity, I'll say entity
equals from, so I don't lose my mind, and then assert that entity is an instance of
dragon treasure. And down here, we need to create a new DTO object. So I'll say DTO
equals new dragon. Dragon treasure API. And remember, the job of load is just to
create the object and put an identifier on it if there is one. So DTO arrow ID equals
entity arrow get ID and then return DTO. And then down here and populate kind of do
the same thing. I'll steal a couple lines from above that set the entity variable. So
say DTO equals two, and one more assert that DTO is going to be an instance of.

I've just created that class, it helps us map from the entity to the DTO, and our state provider is using Micromapper internally, so it should just use that. And it does! So with just the API Resource class and just this one mapper, we now have a database-powered but custom API Resource class. Woo! So, let's get a little more interesting now. Another thing that every Dragon Treasure has is an owner, which is a relationship to a user. So in our API, we're going to have that same relationship, but instead of being related to the user entity, we're going to have it related to the user API. So check this out. We'll say public user API owner equals null. And our job now is to populate that over in our API mapper. So down here, DTO arrow owner equals, but hold on a second. It's not as simple as just saying entity arrow get owner, because entity arrow get owner is a user entity object. What we need is a user API object. So this is really cool. We're actually going to use the micro mapper to convert this user entity object into a user API object. And we can do that because we already have a mapper defined for that. So up here on top, we'll actually inject private micro mapper interface, micro mapper. And then down here, we'll say DTO arrow owner equals this arrow micro mapper, arrow map, will map entity arrow get owner, which is that user entity object to user API colon colon class. How cool is that? Now one thing to be aware of is that if in your system entity get owner might be null, then you'd want to code defensively here. What I mean is you would say something like, if you have an owner, then map it else pass null, something like that. Or maybe you don't set the owner at all if it's null. But in our case, we're always going to have an owner. So this should be safe. All right, so let's refresh. And oh, look at that. We have an owner field and it's showing up as an IRI. Why is it showing up as an IRI? Because a pet platform recognizes that the user DTO, the user API object is an API resource. And how does it show API really API resources that are relations by default, it sets them as the IRI. So that's exactly what we wanted to see. Alright, so let's fill in the rest of the fields here. I'll do this really quickly. Now it's one of these fields I'm doing is called short description. That was actually a kind of a custom field we had in our old API. So we'll see how that's a lot simpler now. And another custom field that we had was is mine, which is now just going to be a very normal property. And we'll see how that's defined in a second. And over in our mapper, we just need to set this stuff, which again, I'll do really quickly. Most of this is normal. But here for the short description, the way that was handled before is in Dragon Treasure, we have a get short description methods, we actually had this as an API as a custom API property, calling that getter. Now it's actually really simple, it's just going to be a normal property like anything else. And then we'll handle setting the custom data when in our mapper. So the short description is equal to entity arrow get short description. And finally, for DTO arrow is mine, this is supposed to be if the currently authenticated user owns this, it's kind of hard to code that to true just for a second. All right. So if we go over and refresh now, oh, that is beautiful. And to really prove it, let's try one of our tests. So we have tests functional dragon treasure resource test. And we do have a test get collection of treasures, this test to make sure that we only see the published ones. So this is actually going to make sure guarantee that our, our is published extension still working. And it checks to make sure we have all the correct keys. So we'll say symphony php bin slash php unit dash dash filter equals and look at that it passes immediately. This gets me really excited. All right, before we finish, though, let's actually fix this is mine hard codedness. This is easy, but shows off how custom how nice it is to deal with custom fields. In our mapper, this is a service. So we can inject other services like the security service. And then we can just populate that with whatever data we want down here. So is mine is equal to this arrow security arrow, get user. So if we if there is a currently authenticated user, and if that user equals the second treasure get owner, which is a user object, then it's ours. Run the test one more time to make sure that is looking good. And it is. All right, next, I want to go a little bit deeper into this idea of having relationships in our API. Because it's a problem with the cool solution, but also one that can cause recursion if you're not careful.
