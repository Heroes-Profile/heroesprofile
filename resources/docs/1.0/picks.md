> {warning} We **DO NOT** consider The **[Heroes Profile Drafter](https://drafter.heroesprofile.com/)** to be a perfect predicter of draft.  It was built to investigate and gauge compositions.  Please use the data to help supplement your draft knowledge.

# Heroes Profile Picks Overview

---

- [Picks 1,2](#picks-1-2)
- [Picks 3,4,5,6,7,8,9,10](#picks-3-10)

<a name="#picks-1-"></a>
## Picks 1,2
We take the <a href="/docs/1.0/terms#influence" target="_blank">Influence</a> value and scale it to draft order.  This is what we show as the first pick for each team.  <a href="/docs/1.0/terms#influence" target="_blank">Influence</a> is a value that attempts to take into account win rate scaled with how often it is picked or banned.

<a name="picks-3-10"></a>
## Picks 3-10
We use our composition data scaled with draft order for these picks.  Given the teams picks, we pick up the most popular composition with the picked heroes, and from that, we pick up the most popular heroes played in that composition.  

---

 An example.  Lets say most popular composition is `Tank, Bruiser, Healer, Ranged Assassin, Ranged Assassin`.  If a team first picks Orphea, the drafter will see that the most popular composition with Orphea is going to be the one defined above and try and find a composition of heroes that works with that.  In that scenario, Diablo, D.Va, Brightwing, Li-Ming, and Greymane are popular choices, and will be near the top.  If the team then picks The Lost Vikings, the drafter will go find popular compositions that include the `Support role, Ranged Assassin` and find the most played heroes scaled to draft order pick rate for that composition and show those as best draft picks.  This approach continues until the draft is done.

> {info} The **[Heroes Profile Drafter](https://drafter.heroesprofile.com/)** is best viewed and used on the computer.  While we have made efforts to make it mobile accessible, there is a lot of information to display, and it is not entirely mobile friendly.
