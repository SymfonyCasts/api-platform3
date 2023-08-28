# Collection Relation Write

Coming soon...

We are now so close to completely re-implementing our API using these custom classes.
Let's run all of our tests and kind of see where we stand. And oh, so close!
Everything except for one. The one failing test is coming from UserResourceTest. Test
treasures cannot be stolen. Let's take a look at that. UserResourceTest. Test
treasures cannot be stolen. All right, so in this test, we are updating a specific
user and trying to change their DragonTreasures property to set to someone else's
treasure. So the point is, in this case, we're talking about writing a collection
relation property in our API. Now, first of all, I might recommend against allowing
collection relationship properties like this to be modified. It just adds a lot of
complexity to your system, and you need to worry about things like this, where by
setting the DragonTreasures property here, you're actually modifying this
DragonTreasures owner to be somebody else. And there's a different way to do this
already. You could make a patch request to this specific treasure and change their
owner to be a different owner. So we don't need this fanciness of having this
collection relationship being modifiable. So avoid it if you can. You will be
happier. However, if you do need to allow this, then let's find out how to do it. All
right, I'm going to start by actually duplicating this test. Perfect. And we're going
to call this test treasures can be removed. So if you think about it, if you send a
DragonTreasure, actually, let's look up here. Now, let me show you what I mean by
that. We're going to kind of make this test a bit fancier. So we have user and other
user. And I'm going to make this first DragonTreasure owned by user. I'm going to
make a second DragonTreasure owned by this user, but I'm not going to need a variable
there. You'll see why in a second. And then I'm going to make a third DragonTreasure.
We'll call DragonTreasure3 owned by other user. So we have three DragonTreasures. Two
of them are owned by user. One is owned by other user. Now down here, we're patching
to modify this one user. And when we do, actually going to remove username, I don't
really care about that. We're going to send two DragonTreasures to it, the first
DragonTreasure. And the third DragonTreasure. DragonTreasure3 get ID. So momentarily,
this test is actually going to test two things. It's first in a test to see that this
second DragonTreasure is removed. So if you think about it, since user started with
these two treasures right here, the fact that this second treasure's IRI is not being
sent means that we actually want that to be removed from the user. And that's
actually what we're really testing in this. That's really what I want to test in this
test. I threw in this DragonTreasure3 to temporarily actually show that treasures can
be stolen. This is currently owned by other user. We're going to pass it down here
and verify that it is actually that the owner of this DragonTreasure3 actually
becomes user. So it actually changes from other user to user. That's actually not the
end behavior we want, but I want to get all the writing of this relation working
first, then we'll worry about preventing that. So down here, we're going to assert
the status code to 100. We actually want this to be allowed. And then I'm going to
extend the test here. We'll fetch slash API slash users slash user or get ID. We'll
fetch this user. I'm going to dump this so we can see it. And we're just going to
assert that we get the results back that we want. So for example, the length of
DragonTreasures, the DragonTreasures field. I need to put that in quotes should be
two, because we should have treasures kind of one and three there. And assert that
DragonTreasures zero. Is equal to slash API slash treasures slash, and it should be
this first one here. So DragonTreasure arrow get ID. And I'll copy that and duplicate
it. We expect the other DragonTreasure to be actually DragonTreasure3. Perfect. And
by two, I meant three. There we go. So a bit of a complicated test there, but a
really nice one to make sure that DragonTreasure2 here is going to ultimately be
removed. And DragonTreasure3 is actually going to have its owner changed to this
user. So let's try this test. Dash dash filter equals test treasures cannot be
removed. And by cannot be removed, I of course, I mean, can be removed. Some good
copy paste madness right there. There we go. All right. And it fails and it see it's
failing down here and user resource test line 81. Because this request is successful,
but ultimately. The two DragonTreasures we have are still the original two. You can
see slash API slash treasure slash two instead of slash API slash treasure slash
three. So really, I think no changes were made to the treasure. So to see what's
going on here, let's go up to the mapper for this. This is going to be user API to
entity. So we're going to make this patch request that will take this data and put it
onto the user API. And then when we're mapping, you want to see what's happening with
that user API object when we're mapping it to the entity. And of course, the reason
DragonTreasure isn't changing at all in the database is because we're not even
mapping that from the DTO to the entity. We kind of left that as a to do. So down
here, let's actually dump DTO to at least see what our DTO looks like after.

This is the first time I've seen this. The key thing to see here is that there are still two dragon treasures in the DTO and there are still the original two. 1 and 2. So this tells me that these two dragon treasures here that we're sending, this field is actually being totally ignored. It should be changing to 1 and 3. And the reason, some of you may be screaming at me, is that inside of UserAPI, the dragon treasures property is not even writable. So kind of cool to see that writable false doing its job and not making that writable. And now when we spin over and try it, you'll see the difference. Perfect. Lookit. Two treasures, but ID 1 and ID 3. So our UserAPI is now updated correctly. Our test is still failing because we are not actually doing anything with those DTOs. We need to set that back onto our user entity. So in this case, what we basically need to do is take an array of dragon treasure API objects and map them to dragon treasure objects so we can set that onto the user object. So once again, we need our mapper inside of here. So we'll head to the top. Missed enough times now. Private micro mapper interface, micro mapper. And back down here, I'm going to say dragon treasure entities equals an array. And I'm going to keep it real simple this time. I'm going to use a good old fashioned for each. We're going to loop over DTO arrow dragon treasures as dragon treasure API. I'm keeping my variables very clear here so I can keep my API and my entity objects straight in my head. And then dragon treasures entities, we're going to append that array with this arrow micro mapper arrow map. Passing our dragon treasure API, and we want to map that to dragon treasure colon colon class. As you can probably guess, I'm going to make sure that I also pass a micro mapper interface max step set to zero. Again, zero is fine here because we just need to make sure that the dragon treasure mapper just queries for the direct, the correct dragon treasure entity. If we were allowing embedded data to be passed, then we'd actually want to have a max step of one so that the actual individual properties of each dragon treasure API are mapped onto the dragon treasure. We don't care that. We just need to make sure we have the right entity object from the database. All right, and then down here, I'm going to just DD dragon treasure entities. Let's see how that looks and okay, it looks good. We have two dragon treasure ID one queried from the database to get that. And down here, dragon treasure ID three. All right, the last thing we need to do is just set that on the user entity. So we'll say entity arrow sets, but oh, there is no set dragon treasures method. And that's by design. If you look inside of your user entity, there's a get dragon treasures method. There's no set dragon treasure method. Instead, there's an add dragon treasure method and a remove dragon treasure method. Now, I'm not going to get into too deeply why we can't have a setter. That has to do with setting the owning side of the doctrine relationship. But the point is, we need to call the adders and the removers. And it's actually a little more complicated than that, if you think about it. What we really need to do is look at what dragon treasure entities we have here. What dragon treasure entities are already on this field like one and three. And then call the correct adders and removers. So in our case, specifically, we're going to want to call the remove dragon treasure method for this middle one, and the add dragon treasure for this third one. So you almost need to do like a diff between the new entities and the existing dragon treasure entities and call the adders and removers accordingly. That sounds annoying and tricky. And fortunately, there's already something in symphony that can do that, a service called the property accessor. So head up here, and we're going to add private property accessor interface, property accessor. This is a cool service. Property accessor is good at just setting properties that can detect if a property is a setter method, but can also detect this adder and remover methods. So here we're gonna say this arrow property accessor arrow set value, we're gonna pass it the object that we're setting data on to which is our user entity. The property path, dragon treasures, and then finally, the value on their dragon treasure entities. And let me keep my DD entity down here. Watch when we run this, scroll up, here's our user object and look at dragon treasures. It has two ID one and ID three. It correctly updated the dragon treasures property. How the heck did it do that? By calling the adder and remover methods. So it's actually doing that diff of the new dragon treasures and the existing dragon treasures here and calling the adder and remover method. Watch I'll prove it. I'll dump down here, removing treasure. Treasure arrow get ID. When we run the test again, there it is removing treasure two, it detects that that one is missing from the new entities, it calls the remover, life is good. So let's remove this dump. And this other dump inside of there. And now the test passes, we can see the response, final response, every fetch it, we get one and three back. What happened to two, two was actually deleted from the database entirely. Behind the scenes, its owner was set to null. And then thanks to a orphan removal, anytime one of these dragon treasures is owner is set to null, it actually gets deleted. It's called an orphan. That's something we talked about in a previous tutorial. All right, so this is awesome. Our dragon treasure is now writable. And one thing I want to clean up here is this is test treasures can be removed. So let's actually remove the part where we are stealing dragon treasure three. So I'll remove that object there, I'll remove setting it down here. We'll change the length down to one and just test that one. So now it's truly a test just about being able to remove an entity, a dragon treasure. Still passes, and I'll get rid of my dump. So next, let's look back at test treasures cannot be stolen. As it turns out, they can be stolen. But oh, we're so close to getting this polished off. We're just missing a custom validator that we created in a previous tutorial. We're going to fix that next. And good news, when we do, that validator is actually going to be a lot simpler in the new DTO system. So let's check that out.
