> {warning} We **DO NOT** consider The **[Heroes Profile Drafter](https://drafter.heroesprofile.com/)** to be a perfect predicter of draft.  It was built to investigate and gauge compositions.  Please use the data to help supplement your draft knowledge.

# Heroes Profile Drafter Terms

---
- [Adjusted Pick Rate](#adjusted_pick_rate)
- [Ban Rate](#ban_rate)
- [Draft Order Pick Rate](#draft_order_pick_rate)
- [Games Banned](#games_banned)
- [Game Map](#game_map)
- [Games Played](#games_played)
- [Hero Level](#hero_level)
- [Hero Rank](#hero_rank)
- [Heroes Profile](#heroesprofile)
- [HP Draft Value](#hp_draft_value)
- [Influence](#influence)
- [Player Rank](#player_rank)
- [Pick Rate](#pick_rate)
- [Region](#region)
- [Role Rank](#role_rank)
- [Timeframe](#timeframe)
- [Timeframe Type](#timeframe_type)
- [Win Rate](#win_rate)
- [Win Rate Confidence](#win_rate_confidence)


<a name="adjusted_pick_rate"></a>
## `Adjusted Pick Rate`
This is similiar to [Pick Rate](#pick_rate) except it takes into account ban rate.
```json
(Games Played / Total Games * 100) / (100 - Ban Rate)
```

<a name="ban_rate"></a>
## `Ban Rate`
Ban Rate is calculated by taking the total number of games banned, and dividing by the total number of games played.
```json
(Banned/Total Games)  * 100
```

<a name="draft_order_pick_rate"></a>
## `Draft Order Pick Rate`
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
| 8 | Ban  | 5  |
| 9 | Ban  | 5  |
| 10 | Pick  | 7  |
| 11 | Pick  | 5  |
| 12 | Pick  | 3  |
| 13 | Pick  | 3  |
| 14 | Pick  | 3  |
| 15 | Pick  | 1  |

In this example, we can see that of the games Li-Ming is banned or picked, she is most banned/picked in the 5th draft position (first hero pick) and least banned/picked in the last draft position.

<a name="games_banned"></a>
## `Games Banned`
Total number of games a hero is banned.

<a name="game_map"></a>
## `Game Map`
All of the game maps in the current rank rotation.

<a name="games_played"></a>
## `Games Played`
Total number of games a hero is played.

<a name="hero_level"></a>
## `Hero Level`
A filterable option to only show games where players were a given hero level.  If you were interested in seeing a draft where all players had to be hero levels 40-60, you could choose that in this filter.

<a name="hero_rank"></a>
## `Hero Rank`

<a name="heroesprofile"></a>
## `Heroes Profile`

<a name="hp_draft_value"></a>
## `HP Draft Value`

<a name="influence"></a>
## `Influence`
```json
(Wins / Games Played - .5) * Adjusted Pick Rate * 10000
```
<a name="player_rank"></a>
## `Player Rank`

<a name="pick_rate"></a>
## `Pick Rate`
Of the total games played, how often is a hero picked.
```json
Games Played / Total Games * 100
```

<a name="region"></a>
## `Region`

<a name="role_rank"></a>
## `Role Rank`

<a name="timeframe"></a>
## `Timeframe`

<a name="timeframe_type"></a>
## `Timeframe Type`

<a name="win_rate"></a>
## `Win Rate`

<a name="win_rate_confidence"></a>
## `Win Rate Confidence`



> {info} The **[Heroes Profile Drafter](https://drafter.heroesprofile.com/)** is best viewed and used on the computer.  While we have made efforts to make it mobile accessible, there is a lot of information to display, and it is not entirely mobile friendly.
