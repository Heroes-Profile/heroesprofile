> {warning} We **DO NOT** consider The **[Heroes Profile Drafter](https://drafter.heroesprofile.com/)** to be a perfect predicter of draft.  It was built to investigate and gauge compositions.  Please use the data to help supplement your draft knowledge.

# Heroes Profile Drafter Usage

---
- [Step 1 - Filters](#step-1)
- [Step 2 - Team First Pick](#step-2)
- [Step 3 - Draft Load](#step-3)
- [Step 4 - Picks/Bans](#step-4)
- [Picks/Bans Explanation](#pick-ban-explanation)
- [Search Heroes](#searching)
- [Filtering Roles](#roles-filter)
- [In Draft Filters](#filters)

<a name="step-1"></a>
## Step 1
Navigate to The **[Heroes Profile Drafter](https://drafter.heroesprofile.com/)**  and choose your filter options.  Currently only data for patches 2.52 and greater are available for use due to data caching.  Be aware that some filter options may reduce the overall fidelity of the data used as there may be a small amount of games played with those filter options.  Please be cognizant of information such as Total <a href="/docs/1.0/terms#games_banned" target="_blank">Games Banned</a>, <a href="/docs/1.0/terms#games_played" target="_blank">Games Played</a>, and <a href="/docs/1.0/terms#win_rate_confidence" target="_blank">Win Rate Confidence</a> in the pop-ups.

<a name="step-2"></a>
## Step 2
Make sure to select which team has first pick.  Team 1 is the team on the left, and Team 2 is the team on the right.  You are trying to match the tools draft UI up with in-game draft.  This is only important for visualization.  Then hit start draft.

<a name="step-3"></a>
## Step 3
Immediately after hitting "start draft" the tool will go out and fetch the best ban options given the <a href="/docs/1.0/terms#filters" target="_blank">Filters</a> you chose.  Once it has calculated the best bans, it will order them from best to worse by left-to-right, and top-to-bottom.  Furthermore, Heroes the drafter considers to be more popular/strong at that position will have a star next to their hero image.  Highlighting hero images will give you more information about the hero.  You can see an explanation of what those values mean at [Terms](/{{route}}/{{version}}/terms).

![image](/images/drafter/heroes.JPG)


<a name="step-4"></a>
## Step 4
The **[Heroes Profile Drafter](https://drafter.heroesprofile.com/)** will automatically switch between Team 1 and Team 2 Bans/Picks throughout the draft.  All the user has to do is make their picks.

<a name="pick-ban-explanation"></a>
## Picks/Bans Explanation
See [Picks](/{{route}}/{{version}}/picks) and [Bans](/{{route}}/{{version}}/bans) for explanations on how picks and bans are determined.

<a name="searching"></a>
## Search
The Search input can be used to search for a specific hero.  The order of the Heroes will not change when searching.

![image](/images/drafter/search.PNG)

<a name="roles-filter"></a>
## Filtering Roles
The Role Images can be clicked to filter for a specific role.  The order of the Heroes will not change when filtering.

![image](/images/drafter/roles.PNG)

<a name="filters"></a>
## In Draft Filters
The filters chosen to setup your draft can be changed at any time during the draft by toggling the "Show Filters" button.  If you change any options, the data for your current pick will **NOT** change for that filter change, but the data for your next pick **WILL** be based off that change.

![image](/images/drafter/show.PNG)

To hide the filters, simply hit the "Hide Filters" button.

![image](/images/drafter/hide.PNG)

> {info} The **[Heroes Profile Drafter](https://drafter.heroesprofile.com/)** is best viewed and used on the computer.  While we have made efforts to make it mobile accessible, there is a lot of information to display, and it is not entirely mobile friendly.
