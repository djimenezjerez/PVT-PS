Run, sigepmv2018.exe
WinWait, Error Fatal, 
IfWinNotActive, Error Fatal, , WinActivate, Error Fatal, 
WinWaitActive, Error Fatal, 
MouseClick, left,  352,  134
Sleep, 100
WinWait, SISTEMA DE GESTION PUBLICA, 
IfWinNotActive, SISTEMA DE GESTION PUBLICA, , WinActivate, SISTEMA DE GESTION PUBLICA, 
WinWaitActive, SISTEMA DE GESTION PUBLICA, 
MouseClick, left,  224,  135
Sleep, 100
WinWait, SigepMV V.6.0.0-[0345-DE-Mutual de Servicios al Policía], 
IfWinNotActive, SigepMV V.6.0.0-[0345-DE-Mutual de Servicios al Policía], , WinActivate, SigepMV V.6.0.0-[0345-DE-Mutual de Servicios al Policía], 
WinWaitActive, SigepMV V.6.0.0-[0345-DE-Mutual de Servicios al Policía], 
MouseClick, left,  51,  83
Sleep, 100
MouseClick, left,  727,  514
Sleep, 100
MouseClick, left,  75,  152
Sleep, 100
MouseClick, left,  61,  378
Sleep, 100
MouseClick, left,  545,  319
Sleep, 100
Send, beneficiarop{TAB}{TAB}documento{SPACE}{BACKSPACE}{TAB}{TAB}{TAB}12163
Sleep, 100
Send, 1216323.01
Sleep, 100
Send, 2710110.8{ENTER}
MouseClick, left,  460,  283
Sleep, 200
Send,  121631216330051{RIGHT}706.91{ENTER}{DOWN}{LEFT}
Sleep, 200
Send,  121631216330029{RIGHT}1546.35{ENTER}{DOWN}{LEFT}
Sleep, 200
Send,  121631216330013{RIGHT}770.65{ENTER}{DOWN}{LEFT}
