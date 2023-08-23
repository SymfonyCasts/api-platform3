# Pagination

Coming soon...

When we fetch the collection for our quests, we see all 50 items that we're
returning. There is no pagination and you can see it because at the bottom we don't
see any extra data about the pagination. As a reminder if we go to if you go all the
way down to the bottom there actually is an area that describes how the pagination
works showing you that in this case there's three pages but over here on our quests
there is no such stuff on the bottom. That's because pagination is ultimately the
responsibility of your provider. So it works pretty simple. If your collection
provider returns just an array of quests or some sort of iterable of quests, then
that's what's going to be returned to the screen. But if it returns an object from
API Platform that implements a paginator interface, API Platform will see that and it
will render it with all the pagination details. So it's pretty simple. Instead of
returning this array, we're going to return a an object that implements that
paginator interface. And actually API Platform has one that we can just use. So check
this out. We'll say quest equal and then return new traversable paginator from API
Platform. This takes a couple of arguments versus the the traversable, the thing that
holds the results. So that's our array of quests in this case. Oh except it needs to
be an iterable of quests. So we're going to this case. Oh except it needs to be an
iterator. So new array iterator and we'll put the quests inside. Then the current
page, I'm going to hard code that as one for now. The items per page, I'm going to
hard code that as 10. And the total items, which for now I'm just going to count the
quests. Now it's not a very smart paginator yet. It's not actually going to work
since we have the page hard coded. But if we go over and refresh and go to the
bottom, we do see the pagination information. And you can see according to this, it
says that we can go all the way to the last page, which will be page five, which
makes sense. We're doing 10 per page, we have 50 items, so five pages. But you'll
also see that we're still actually returning 50 items. There's not actual pagination
going on here. There's no magic happening behind the scenes. We're passing this all
50 items, it's rendering all 50 items. So I'm going to do a little bit of
reorganization here to get this ready to actually work the way that we want it to. So
up here, I'm gonna set a couple of variables. First one is the current page, I'm
going to keep that hard coded to one items per page. I'm gonna keep that hard coded
to 10. And total items. Now for this, I'm going to call a new private method called
count total requests. Over here, I'll hit Alt Enter, and I can add that method down
here. This is going to return an int. And I'm just going to return 50. 50 is just the
number, we sort of have a fake database, and we've decided there are 50 of them in
the database. I'll change the create quest up here to call that. Now, that might seem
silly, why am I creating this private method that returns that? What I'm trying to
show is that we're going to have two responsibilities with pagination. One, as we'll
see in a few minutes, as we'll see in a few minutes, it's going to be our job to
return the correct subset of our 50 results. Like if we're on page two, maybe we're
returning results 11 through 20. Secondly, independent of that, we're going to need a
way to count the total results. When we use doctrine, what actually happens is
there's one query to return just the results you want. And there's a whole second
query which counts all of the results inside of your table. So this is going to be
kind of our fake method here that you would fill in with real logic to count the
total number of results in your database or wherever you're storing these. All right,
then up here, let's use those variables. So current page, items per page, and total
items. Cool, that doesn't change anything, just a little bit of reorganization. So
what we really need to do is we need to grab just the results that are for the
current page. Like if we're showing 10 per page and we're on page two, we would want
to actually return just results 11 through 20, not all 50 results. But how do we even
determine what page we're on right now we have the page hard coded. If you look over
here, the way that API platform does pages is with query parameters. So technically,
we say question mark page equals two, and we're on page two. But our code isn't
reading this yet. So really, if you look, it still thinks we're on page one, see the
current page down here is just page one. Because we have one hard coded. So how do we
read the page? Now you might think that we need to actually read this query parameter
directly. And you could do that. But API platform has a service available that
already holds all the pagination information. And it's just kind of takes care of a
lot of the work for us. So up here, we're gonna add a second constructor argument
called private pagination. See from API platform, pagination. And down here, we can
replace our local variables. With current page equals this arrow pagination arrow get
current page, or get page. And then that takes the context that we already have as an
argument on this method. And then items per page, this arrow pagination arrow. This
is called get limit, as in like kind of limit offset from a database. And so we'll
pass that operation and context. And then finally, for total items, the next thing we
can actually get is the offset. We can do that with this arrow pagination, arrow get
offset operation, and context. So if we're on page two, and the limits 10, this is
going to offset is going to tell us to start on result 11. And down here, let's just
dump all this current page, items per page. Offset and share. Well, dump total items
as well, even though that's hard coded. Alright, so check this out. I'm gonna go back
to page one right now, refresh. Look at that. Page one, 30 items per page, the limit
is, and the offset is zero. If I go to page equals two, then it's page two, the
number per page is still 30. And so the offset is 30, we should start at result 30.
Now, where's it getting this 30 as the items per page? Well, that's the default in
APL platform for any resource. But this is something that you can configure on your
API resource attribute, you can say pagination items per page. How about let's do 10.
Now check this out. That changes the 10, you can see offsets 10. If we go to page
three, of course, our per page is still 10. And now it's saying, hey, since we're on
page three, you should start at result 20. So now we're dangerous. We've asked API
platform to grab all the pagination information for us. So now we just need to use
that to actually return the correct request instead of all the quests. So to do that,
I'm going to pass the offset and the items per page and to create quests. And down
here, this becomes int offset, and really int limit. And we'll say limit, we'll give
it a default of about 50. And then down here, we're gonna say I equals offset, and
then I is less than or equal to not all of them are really offset plus limit. So it's
kind of a doing sort of a limit offset, as if we were in a database. And now check
this out, we're gonna page three, look at this, we're starting with the items that
are on page three, it's a little more obvious, if we go to page one, you can see
descriptions here, description one, it keeps coming up description to page two,
description 10, description 11, and so on, it works. So I think you kind of need to
watch out for with my kind of silly example here is the, this takes care of the
collection, we still need to kind of do the item. So right now, what we do is really
kind of query the database specifically for the day string. Right now, I'm actually
gonna put zero, and this arrow count total requests. So it's still gonna return all
the requests in this all the quests in this case, and then just find the one that we
want. In a real app, you can make that a little bit smarter. But that's fine. So
that's page nation and a custom resource. What about filtering? We're gonna talk
about creating custom filters in a future tutorial. But spoiler alert, the filtering
logic is also something that happens right here inside of the collection provider.
All right, next up, let's remove all of the API resource stuff from our user entity
and add it to a new class that's going to be dedicated to our API.
