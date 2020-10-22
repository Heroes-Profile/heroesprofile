> {warning} We **DO NOT** consider The **[Heroes Profile Drafter](https://drafter.heroesprofile.com/)** to be a perfect predicter of draft.  It was built to investigate and gauge compositions.  Please use the data to help supplement your draft knowledge.

# Heroes Profile Bans Overview

---

- [Bans 1,2,3,4](#bans-1-4)
- [Bans 5,6](#bans-5-6)

<a name="bans-1-4"></a>
## Bans 1,2,3,4
Bans in the first 4 slots are based entirely on what heroes are the most banned heroes in the game for the given filters **scaled** by which heroes are most banned in the specific draft position of the draft.

---

To show how Draft Pick Rate can affect which heroes are show in a draft position see example below.  Lets assume we are at draft pick position 1.

Lets assume the Top Bans are:
1. D.Va       `200 Games Banned`
2. Imperius   `150 Games Banned`
3. Chromie    `76 Games Banned`
4. Orphea     `25 Games Banned`

We then scale those games banned by how often those heroes are banned in the current draft position.  Lets say D.Va is banned in the current drat position `5%` of the time, Imperius `20%` of the time, Orphea `2%`, and Chromie `57%`.  This means we would be scaling their games banned by those values to get a new top bans list, and HP Draft Value

1. Chromie    `76 * .57 = 41.61 Games Banned at position`
2. Imperius   `150 * .20 = 30 Games Banned at position`
3. D.Va       `200 * .05 = 10 Games Banned at position`
4. Orphea     `25 * .02 = .5 Games Banned at position`

What we see, is that the ban order changes significantly depending on which heroes are banned more often in the particular draft position.

<a name="bans-5-6"></a>
## Bans 5-6
Bans in the 5 and 6 Draft Position work exactly the same as 1-4, except it also looks at the compositional data for the opposing team to see what their next best pick is, and suggest that as a good ban option.

> {info} The **[Heroes Profile Drafter](https://drafter.heroesprofile.com/)** is best viewed and used on the computer.  While we have made efforts to make it mobile accessible, there is a lot of information to display, and it is not entirely mobile friendly.
