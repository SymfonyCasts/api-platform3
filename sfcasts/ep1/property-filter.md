# Property Filter

Coming soon...

Let's add one more filter. We can, so we have three right now, but maybe we wanna
filter actually on the value, maybe like between a range. So there's another filter
built in for that called the range filter. So I'll find the value property and just
like before we use API filter and then range filter, the one from O rm, colon, colon
class. And this one doesn't need any other options. Dang, that's easy. Now when we
refresh and open it up, try it out. Look at that. A whole bunch of new ones. Value
between value greater than, greater than or equal. So let's do value greater than, I
don't know, I don't remember how, what my values are when I execute. You can see it
on the URL a little. The URL encoding going looks ugly cuz the URL encoding, but it
works if I look down here. Cool. Apparently that just return 18 results. Alright, the
last filter I wanna show you kind of isn't a filter at all. It's a way for us to
allow our API clients to choose which fields they want returned. So to show this off,
find your get description method and let's pretend that we wanna return a short
version of the description, like a truncated version. So to do this, I'm going to
copy the get description method, create a new thing called get short description,

And then I'm going to, and then we'll truncate this. We're gonna use the you function
from Symphony. So type you and make sure I should hit tab to ought to complete that.
This is a rare function that we have in Symphony. And hitting tab did add a U
statement for it. So say U. And then we have a bunch of nice methods on this. One of
'em is called Truncate and we'll truncate at 40 characters with a little dot, dot,
dot. Cool. So right now, this is a perfectly functional normal PHP method to expose
this to our API above this, we just need to add the group's annotation uh, attribute
with Treasure colon Reed. Beautiful. So to check this out, I'm actually gonna cheat
and go back to our slash api slash tra tra. Actually head back to your documentation
and refresh. And if you open the get open, the get end point hit try it out. There we
go. Execute and beautiful short description shows up nicely. Now the only kind of
weird thing here is that we have two fuels. We're showing these short description and
then also the description. If our API client wants the short description, they might
not want us to also turn the description just to save bandwidth or something. So
that's a bit wasteful. So to help with this, one thing we can do is use the property
filter. So go back to Dragon Treasure. This is a filter that has to go above the
class.

So type API filter, and then property filter. In this case there's only one of them,
colon, colon class. And there are some options that you can pass to this. You can
look at the documentation, but it doesn't need any options. So what does that do?
Well, if you go and refresh the documentation and look at the collection endpoint,
and let's actually hit try it out, there's a new properties thing here and you can
add a string item to it. So let's add a new one called name and another one called
description. Cool. Down here I'll have execute and you can see it just pops us onto
the U url like normal. But look at the response. It only contains the name and
description fields, okay? It contains the Jsun LD fields. It will always contain
those. But the real data is just those two fields. It's still returning if we look
all 40 items, but only those two fields. If we removed those so that there was none
of those, we're gonna get the normal response with all of them. So by the default,
you get all the fields, but if you want to control which fields you get, you can do
that. Now if you look at the API platform documentation about the property filter,
they actually

Recommend it still works, but they recommend you look at a different solution. And
it's something called Vulcan. This is a protocol for your web server that actually
adds features to your web server and it's created by the API platform team.

They actually have a really good example of how it works. Let's see down here a
little bit. So let's pretend that we have the following api. If you make a request to
slash books, you get these two books back. Okay? So then maybe you make a request to
get more information about the first book. You make a request to that you will. And
here's what that looks like. Okay? Now to get more requests to the, for the author,
you make a request to this url and that's, that looks like. So you can see in all,

If you make a request to slash books to get all the information that we might need,
you actually need to make four requests, the original request, and then you kind of
make this request and this request and then the request for the author. So that was
four requests. So what Vulcan set allows you to do is just make this first request,
but then tell the server that it should push the data from the other requests to you.
The way this looks is probably best seen in JavaScript. So down here is a little
JavaScript example. It's really easy. All you need to do is when you use your
JavaScript, use the fetch function. You say, I want to fetch slash books slash one,
and then you add this special preload header. And actually a better example of the PR
preload is up here, this preload slash member slash star slash author. What that's
basically gonna tell your server to do is look at any URLs that kind of match that
pattern and follow them. I'm not going into the specifics on this, but slash member
slash star is going to match slash member slash all of these. And then the slash
author is then also going to follow the author key once it fetches those books. The
end result of just passing that preload header is that our API is going to return the
normal response for slash books,

But then it's also going to push the other URLs to you. So you can see here, it's
actually gonna push the data for slash books slash one slash book slash two and slash
author slash one to you. So down here, this is a slight different example where
you're just fetching slash book slash one with preload slash author. When you do
this, your book responses is going to be completely normal. The key thing is that a
second later, if you try to use fetch again on books js o.author, that's gonna return
immediately. That's actually not going to make a second age X request because you
already have that data. So you write your job script basically like normal. All you
need to do is add a new preload header and you get benefit per of the extra
performance. So I'm not gonna go more deeply into that. I wanted you to be aware of
this. It can be a very powerful feature in your api. All right, next, let's talk
about, I want add, I wanna talk about formats. We know that our API can return jsun,
ld, json, and even HTML representations of our representations. Let's add to new
formats, including a CSV format, which is gonna be the fastest CSV feature that
you've ever built.

