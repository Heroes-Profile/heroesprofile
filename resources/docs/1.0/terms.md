> {warning} We **DO NOT** consider The **[Heroes Profile Drafter](https://drafter.heroesprofile.com/)** to be a perfect predicter of draft.  It was built to investigate and gauge compositions.  Please use the data to help supplement your draft knowledge.

# Heroes Profile Drafter Terms

---
- [Draft Filters](#filters)
- [Draft Attributes](#attributes)

<a name="filters"></a>

<a name="game_map"></a>

### Game Map
All of the game maps in the current rank rotation.  

<a name="hero_level"></a>

### Hero Level
Option to only show games where players were a given hero level/s.  If you were interested in seeing a draft where all players had to be hero levels 40-60, you could choose that in this filter.

<a name="hero_rank"></a>

### Hero Rank
Option to filter on different Hero Ranks.  These ranks are calculated using the Heroes Profile Hero MMR and distributions.  This filter uses Heroes Profile's unique Hero MMR value for its rank.

<a name="player_rank"></a>

### Player Rank
Option to filter on different Player Ranks.  These ranks are calculated using the Heroes Profile MMR and distributions.

<a name="region"></a>

### Region
Option to only show games from a specific region/s.

<a name="role_rank"></a>

### Role Rank
Option to filter on different Role Ranks.  These ranks are calculated using the Heroes Profile Role MMR and distributions.  This filter uses Heroes Profile's unique Role MMR value for its rank.

<a name="timeframe"></a>

### Timeframe
Depending on your Timeframe Type choice, you will either be able to choose individual patches, or patches bundled into their Major component.

<a name="timeframe_type"></a>

### Timeframe Type
Either Major or Minor.  Major references the first 3 values in a patch.  So `2.52.1.82169` would be Major Patch `2.52`.  When choosing Major in the filters, you will be choosing all minor patches that start with you Major option.  Minor refers to the individual patches and allows you to choose each patch independently.


<a name="attributes"></a>

<a name="adjusted_pick_rate"></a>

### Adjusted Pick Rate
This is similiar to [Pick Rate](#pick_rate) except it takes into account ban rate.
```json
(Games Played / Total Games * 100) / (100 - Ban Rate)
```

<a name="ban_rate"></a>

### Ban Rate
Ban Rate is calculated by taking the total number of games banned, and dividing by the total number of games played.
```json
(Banned/Total Games)  * 100
```

<a name="draft_order_pick_rate"></a>

### Draft Order Pick Rate
This value is the rate at which a hero is picked at a given pick position in the draft, for all the games they've played.  See example below for Li-Ming's pick rate at each position in the draft

# Li-Ming
| Draft Pick Position # | Ban Or Pick   | Rate %|
| : |   :-   |  :  |
| 1 | Ban | 7  |
| 2 | Ban   | 7  |
| 3 | Ban  | 10  |
| 4 | Ban  | 10  |
| 5 | Pick  | 15  |
| 6 | Pick  | 12  |
| 7 | Pick  | 7  |
| 8 | Pick  | 5  |
| 9 | Pick  | 4 |
| 10 | Ban  | 6  |
| 11 | Ban  | 5  |
| 12 | Pick  | 3  |
| 13 | Pick  | 3 |
| 14 | Pick  | 2  |
| 15 | Pick  | 2  |
| 16 | Pick  | 2  |

In this example, we can see that of the games Li-Ming is banned or picked, she is most banned/picked in the fifth draft position (first hero pick) and least banned/picked in the last three draft positions.

<a name="games_banned"></a>

### Games Banned
Total number of games a hero is banned.

<a name="games_played"></a>

### Games Played
Total number of games a hero is played.

<a name="hp_draft_value"></a>

### HP Draft Value
The Heroes Profile Draft Value is calculated using the data available in the draft.  This value is scaled to distinguish good and bad picks.  More information on how this value is calculated can be found on the [Bans](/{{route}}/{{version}}/bans) and [Picks](/{{route}}/{{version}}/picks) documentation.

<a name="influence"></a>

### Influence
A compiled value of the following attributes.  Wins, Losses, Games Played, Games Banned, and Total Games by all heroes.  This value is scaled to an integer from -1000 to 1000 and indicates the relative power of a hero to all the other heroes.
```json
round((Wins / Games Played - .5) * Adjusted Pick Rate * 10000)
```

<a name="pick_rate"></a>

### Pick Rate
Of the total games played, how often is a hero picked.
```json
Games Played / Total Games * 100
```

<a name="win_rate"></a>

### Win Rate
```json
Wins / Games Played
```

<a name="win_rate_confidence"></a>

### Win Rate Confidence
This value tells you how confident we are with a Win Rate.  If you take this value and add or subtract it from the Win Rate, you can get a range for a heroes Win Rate as an estimate for their true Win Rate.
```json
1.96 * sqrt(((Win Rate / 100 * (1 - Win Rate / 100))/ Games Played));
```



<a name="other"></a>



> {info} The **[Heroes Profile Drafter](https://drafter.heroesprofile.com/)** is best viewed and used on the computer.  While we have made efforts to make it mobile accessible, there is a lot of information to display, and it is not entirely mobile friendly.
