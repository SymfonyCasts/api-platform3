# Custom Resource Provider Part1

Coming soon...

OK, we have our new API Resource class. And for the most part, it works like normal.
We can customize things. For example, instead of DailyQuests, maybe we just change
the short name here to just Quest. Over here, that will change the title up here, and
it changed all the URLs, just like it normally would. All right, so to be able to
load data and have this collection endpoint not return a 404, we need a state
provider. And actually, it's not just the get endpoint. The put endpoint uses the
state provider, and so does the delete and patch endpoints. All three of those will
need to call the state provider to load the entity they're editing or deleting before
actually editing or deleting it. So let's make a state provider. We've done this
before. This will just be a little different since we're not going to hook it up with
an entity. So we'll run bin console make, make state provider. Let's call it
DailyQuest state provider. Awesome name. All right, spin back over, open the state
directory, and there it is. So you've seen this before. Our job is to just return the
DailyQuests for the current page. We're gonna start very simple right now. And I'm
gonna return an array with two hard coded new DailyQuest objects. They're both empty
because that class doesn't even have any properties on it yet. Then we also know that
when you create a provider that's not magically used, we need to tell API platform
that this is supposed to be the provider for our API for our DailyQuest class. So to
that, we'll say provider, and then say DailyQuest stats provider, colon, colon class.
And that's it. All right, let's try it. So I'm gonna go back over, and we're gonna
execute the collection endpoint. And yes, no more 404, it's a 200. And we can see
it's got two items left. And here are the items. Though, all we have right now are
the JSON LD field at ID and at type, which makes sense, right? Because our class
doesn't have any other properties on it. But at this point, I want to go back and
talk about why the get one endpoint is missing. So we have the get collection
endpoint, but we're missing kind of the get a single item endpoint. Why is that? And
the reason that's missing is because every API resource needs what's called an
identifier. And right now, our class does not have an identifier yet. So this
actually causes the two routes to collide. Let me show you. So if we spin over and
run a bin console debug router, one of the cool things about API platform is that for
every operation, it actually dumps a route for that. So let me make this a little
smaller. There we go. So you can see all of our different routes here for our quest.
And check this out. Here's the one for get collection. But check out above it. Here's
the one for the get single, it has the same URL as the get collection. Why? Because
it doesn't know if our API resource has an ID a property called ID that is its
identifier or foo bar or date. So it just kind of generates it with nothing. And
because these have the same URL, this one ends up not showing up in the
documentation. So how do we fix it? Well, the easiest way is to add an ID property.
So back over here, and let's do public int ID. And for simplicity, I'm actually going
to add a constructor here, where we can pass the ID in. Of course, we'll just set
that in there. And over in our provider, we'll just make up a couple IDs right now,
how about four and five. Now watch as soon as we do this, if I run debug router
again, check it out. Here's the get single has a different URL from the get
collection. And look, the ID was also missing from our put patch and delete. Those
all now have that ID. Over here for refresh, we see that same thing. So this idea of
an identifier is really important because it's going to be used in the URL. It's also
what's going to be used when it generates the at ID feel for each item. So check this
out at ID is now pointing to slash API slash quest slash for it recognizes that the
ID is what's used to complete that URL. How did I know that our that our property ID
is the identifier? I'm actually not entirely sure. But it seems that the name ID is a
special name somewhere in API resource in API platform. And if you use ID, then API
platform says, Oh, that must be your identifier. But there's a more explicit way to
say that a property is an identifier. And we're going to see that in a second.
Because in our case, I don't actually want the ID to be I don't want an integer ID as
the identifier, we're going to have a new quest every single day. So I want the
identifier to be the date. So you'd say something like slash API slash quest slash
2023 dash o six dash o five. So check this out over here, instead of that public int
ID, I'm gonna say public date time interface day. And same thing down here, replace
the date, the argument date time interface day. And this error day equals day. And
then our state provider will say how about new date time now, and new date time
yesterday. Now soon as we do that, if we refresh, you'll notice that we're back to
where we were before we're missing the ID on the put, delete and patch and our get
single is gone. That's because it doesn't know that this day is meant to be our
identifier.

But when we try the get collection endpoint, other than that problem, hey, the day actually shows up inside of our output like a normal property. So we want to tell API Platform, hey, this isn't a normal property. This day is our identifier. So the way we do that is we add an API property above this, and we say identifier true. However, that's going to cause its own set of problems. First of all, we can see that, in fact, that does fix all of our routes. Everything seems to look good. But when we try the collection endpoint, we get a 400 error. Unable to generate an IRI for the item of type dailyQuest. So what happened was it loaded our two dailyQuest objects. And when it was trying to generate the at ID property, which is the IRI, for some reason it had a problem doing that. Now, to see what the real problem is, I'm going to go down here to my web debug toolbar and open up that request in the profiler. And on the exception tab, you actually see there were two exceptions on this page. This is a nested exception. There was this top level, unable to generate an IRI, but doesn't really tell us why there was a problem generating the IRI. Or down here, we can see we were not able to resolve the entity identifier matching parameter day. This error is still not very clear, so I'll help us out here. What it's basically saying is when it tried to generate the URL, it couldn't because it tried to transform to create this IRI string. It tried to transform our date time interface object into a string, and you can't convert date time interface objects into strings, so it threw that error. So we've actually chosen a pretty tricky IRI to work with here, which is kind of cool. I need to check this. Now, there is an internal system where you can actually help convert IRIs. You can help convert this to a string and back. But another way to do this, which I really like, is just to create a property for our identifier. So I'm going to create a new function called get day string. This will return a string. And very simply, it will return this error day error format. The format I want is y dash m dash d. Now, to make this the identifier, I'm actually going to move the API property down to here. Yeah, that works. So back over here, our routes still look correct. You can see we have day string now. And when we try to get collection endpoint, check that out at ID slash API slash quest, and then the date. That's exactly what we wanted. Though there is still, it's kind of weird now is that we have a day string field, which we didn't really want. We just wanted that to be used as the identifier. And also the day itself, we don't really need the day there. Again, it's part of the URL. So we're gonna talk more about this later. But effectively, this is a case where we want to kind of hide a couple of fields in our DTO. So above the datetime interface, the way to hide a property entirely from your API. I should mention why I made datetime interface public. Yeah. Is you can use an ignore attribute from symphony serializer. That over here for re execute that, boom, that field is completely gone. It can't be read and it can't be written. And then for the day string, we could do the same thing. But another option here, in this case, is you could say readable, false. So it's not going to be included in the readable, it is still technically writable, though there's no way to actually there's no set day string. So in practice, it won't be readable. So when we execute that, that field is gone as well. That is a setup we want, we have the ID we want, we don't have any extra fields that we don't want right now. And now we can actually add the other fields that we want. So to do that, I'm actually going to create an enum. First, you'll see why in a second, I'm gonna create an enum directory. And inside there, an enum called daily quest stats enum, I'm actually gonna just daily quest status enum, I'm actually gonna copy that in there. So just a way for us to keep track of the status of this daily quest. And then in daily quest, I'm going to use that on my property here. So I'm gonna have a public string, quest name, public string description, just whatever properties I need in my API, and int for called difficulty level. And a public daily quest status enum called status. Nice. Now we're not populating these yet. But if you go over here and execute, you'll see we don't actually get the fields back. Because they're not actually populated. So if we did refresh the page and kind of went down to the documentation, you would see that it does show that these are part of the API. And if they were populated, they would be returned. So let's go into our state provider. And this is where we're going to return them. So I'm actually going to say, return this arrow create quests. Put this into a little private function. And then for that private function, I'm just going to paste that in, you can get from the code block on this page. It's not particularly interesting. In this case, I'm gonna create 50 quests. So I have a little for loop, I'm creating the daily quest, every quest is for one day further back in the past. Some random question, and then just some random data after that. And then some of these quests are active, and some of them will be completed. And then now notice I'm using the day string as the key for this array, we don't actually need to do that. That's going to be handy in a second. But that's actually ignored. All that API platform cares is we return a an iterable some sort of collection of daily quest objects, and it's going to return those doesn't actually care what the key is inside of there. So check it out. Let's go back up. Well, actually go back down my bad. So let's try this out. Execute, check that out 50 items, we've got data on all of them. That is beautiful. All right, next, let's see what we need to do to our provider to get it work for the item operations, meaning when we fetch a single item, which happens for this operation, as well as put, delete and patch.
