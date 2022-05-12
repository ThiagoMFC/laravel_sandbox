This is a collection of random stuff made using Laravel hence the name Laravel Sandbox.

There is a basic CRUD application mimicking social media platforms (post, comment, follow, etc etc etc...)

Battleship-like game (user is randomly assigned a set number of ships with various sizes and he has to guess their locations on the board)

    localhost/api/challenges/battleship-rules -> show game rules
    localhost/api/challenges/battleship-start -> start game
    localhost/api/challenges/battleship-end -> quit game
    localhost/api/challenges/battleship/{hit} -> guess where to hit ({hit} i.e F6, B1, C17)
    localhost/api/challenges/battleship-reveal -> reveal battleship positions and quit game (give up)
    localhost/api/challenges/battleship-hint -> show how many ships left and their sizes

Chess game

    localhost/api/challenges/chess-start -> start game
    localhost/api/challenges/chess-end -> quit game
    localhost/api/challenges/chess-board -> show board
    localhost/api/challenges/chess-move/{piece}/{position} -> move piece ({piece} i.e rook1, knight2, pawn6, queen, king, bishop2, etc.) ({position} i.e A3, D7, C4)

And there is an ever growing collection of small random challenges (RandomController)
