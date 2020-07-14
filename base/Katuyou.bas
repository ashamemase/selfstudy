Attribute VB_Name = "Module1"
Option Explicit

Enum emKatuyou
    jisho = 1
    masu = 2
    te = 3
    ta = 4
    nai = 5
    nakatta = 6
    ba = 7
    shieki = 8
    ukemi = 9
    meirei = 10
    kanou = 11
    ikou = 12
End Enum

Enum emGroup
    godan = 1
    ichidan = 2
    henkaku = 3
    ai = 4
    an = 5
End Enum

Public Sub showKatuyou(katuyou As emKatuyou)
    Select Case katuyou
        Case jisho
            MsgBox "辞書形"
        Case masu
            MsgBox "ます形"
        Case te
            MsgBox "て形"
        Case ta
            MsgBox "た形"
        Case nai
            MsgBox "ない形"
        Case nakatta
            MsgBox "なかった形"
        Case ba
            MsgBox "ば形"
        Case shieki
            MsgBox "使役形"
        Case ukemi
            MsgBox "受身形"
        Case meirei
            MsgBox "命令形"
        Case kanou
            MsgBox "可能形"
        Case ikou
            MsgBox "意向形"
            
    End Select
    
End Sub



Public Sub showGroup(group As emGroup)
    Select Case group
    Case godan
        MsgBox "五段活用"
    Case ichidan
        MsgBox "一段活用"
    Case henkaku
        MsgBox "変格活用"
    Case ai
        MsgBox "形容詞"
    Case an
        MsgBox "形容動詞"
    End Select
End Sub
Sub autoconv()

End Sub

Function Convert(str As String)
    
End Function

Function Masu2Jisho(word As String, group As emGroup) As String
    Dim ret As String, stem As String
    ret = Trim(word)
'「ます」の除去
    stem = Left(ret, Len(ret) - 2)
    Select Case group
    Case emGroup.godan
        stem = ChangeLastLetter(stem, 3)
    Case emGroup.ichidan
        stem = stem + "る"
    Case emGroup.henkaku
        If isHiragana(Right(stem, 1)) = True Then
        stem = ChangeLastLetter(stem, 3) + "る"
        Else
        stem = stem + "る"
        End If
    End Select
    Masu2Jisho = stem
End Function

Function Jisho2Masu(word As String, group As emGroup) As String
    Dim ret As String, stem As String
    stem = Trim(word)
'「～しゃる」の処理
    If Right(stem, 3) = "しゃる" Then
        Jisho2Masu = Left(stem, Len(stem) - 1) + "います"
        Exit Function
    End If
    Select Case group
    Case emGroup.godan
        stem = ChangeLastLetter(stem, 2) + "ます"
    Case emGroup.ichidan
        stem = Left(stem, Len(stem) - 1)
        stem = stem + "ます"
    Case emGroup.henkaku
        stem = Left(stem, Len(stem) - 1)
        stem = ChangeLastLetter(stem, 2) + "ます"
    Case emGroup.ai
        stem = stem + "です"
    Case emGroup.an
        stem = Left(stem, Len(stem) - 1) + "です"
    End Select
    Jisho2Masu = stem
End Function

Function Jisho2Te(word As String, group As emGroup) As String
    Dim ret As String, stem As String
    stem = Trim(word)
'「行く」の処理
    ret = Right(stem, 2)
    If (ret = "いく" Or ret = "行く") And (group = emGroup.godan) Then
        Jisho2Te = Left(stem, Len(stem) - 1) + "って"
        Exit Function
    End If
    
    Select Case group
    Case emGroup.godan
        stem = ChangeLastLetterwithOnbin(stem, emKatuyou.te)
    Case emGroup.ichidan
        stem = Left(stem, Len(stem) - 1)
        stem = stem + "て"
    Case emGroup.henkaku
        If Right(stem, 2) = "くる" Then
            stem = Left(stem, Len(stem) - 2) + "きて"
        Else
            stem = ChangeLastLetter(Left(stem, Len(stem) - 1), 2) + "て"
        End If
    Case emGroup.ai
        If (Len(stem) = 2 And stem = "いい") Or (Right(stem, 3) = "がいい") Then
            stem = Left(stem, Len(stem) - 2) + "よくて"
        Else
            stem = Left(stem, Len(stem) - 1) + "くて"
        End If
    Case emGroup.an
        stem = Left(stem, Len(stem) - 1) + "で"
    End Select
    Jisho2Te = stem
End Function

Function Jisho2Ta(word As String, group As emGroup) As String
    Dim ret As String, stem As String
    stem = Trim(word)
'「行く」の処理
    ret = Right(stem, 2)
    If (ret = "いく" Or ret = "行く") And (group = emGroup.godan) Then
        Jisho2Ta = Left(stem, Len(stem) - 1) + "った"
        Exit Function
    End If
    Select Case group
    Case emGroup.godan
        stem = ChangeLastLetterwithOnbin(stem, emKatuyou.ta)
    Case emGroup.ichidan
        stem = Left(stem, Len(stem) - 1)
        stem = stem + "た"
    Case emGroup.henkaku
        If Right(stem, 2) = "くる" Then
            stem = Left(stem, Len(stem) - 2) + "きた"
        Else
            stem = ChangeLastLetter(Left(stem, Len(stem) - 1), 2) + "た"
        End If
    Case emGroup.ai
        If (Len(stem) = 2 And stem = "いい") Or (Right(stem, 3) = "がいい") Then
            stem = Left(stem, Len(stem) - 2) + "よかった"
        Else
            stem = Left(stem, Len(stem) - 1) + "かった"
        End If
    Case emGroup.an
        stem = Left(stem, Len(stem) - 1) + "だった"
    End Select
    Jisho2Ta = stem
End Function


Function Jisho2Nai(word As String, group As emGroup) As String
    Dim ret As String, stem As String
    stem = Trim(word)
    Select Case group
    Case emGroup.godan
        stem = ChangeLastLetter(stem, 1) + "ない"
    Case emGroup.ichidan
        stem = Left(stem, Len(stem) - 1)
        stem = stem + "ない"
    Case emGroup.henkaku
        If Right(stem, 2) = "する" Then
            stem = Left(stem, Len(stem) - 2) + "しない"
        Else
        stem = Left(stem, Len(stem) - 1)
        stem = ChangeLastLetter(stem, 5) + "ない"
        End If
    Case emGroup.ai
        If (Len(stem) = 2 And stem = "いい") Or (Right(stem, 3) = "がいい") Then
            stem = Left(stem, Len(stem) - 2) + "よくない"
        Else
            stem = Left(stem, Len(stem) - 1) + "くない"
        End If
    Case emGroup.an
        stem = Left(stem, Len(stem) - 1) + "じゃない"
    End Select
    Jisho2Nai = stem
End Function

Function Jisho2Nakatta(word As String, group As emGroup) As String
    Dim ret As String
    ret = Jisho2Nai(word, group)
    Jisho2Nakatta = Left(ret, Len(ret) - 1) + "かった"
End Function

Function Jisho2ba(word As String, group As emGroup) As String
    Dim ret As String, stem As String
    stem = Trim(word)
    Select Case group
    Case emGroup.godan, emGroup.ichidan
        stem = ChangeLastLetter(stem, 4) + "ば"
    Case emGroup.henkaku
        If Right(stem, 2) = "する" Then
            stem = Left(stem, Len(stem) - 2) + "すれば"
        Else
        stem = Left(stem, Len(stem) - 1)
        stem = ChangeLastLetter(stem, 5) + "れば"
        End If
    Case emGroup.ai
        If (Len(stem) = 2 And stem = "いい") Or (Right(stem, 3) = "がいい") Then
            stem = Left(stem, Len(stem) - 2) + "よければ"
        Else
            stem = Left(stem, Len(stem) - 1) + "ければ"
        End If
    Case emGroup.an
        stem = Left(stem, Len(stem) - 1) + "ならば"
    End Select
    Jisho2ba = stem
End Function

Function Jisho2Shieki(word As String, group As emGroup) As String
    Dim ret As String, stem As String
    stem = Trim(word)
    Select Case group
    Case emGroup.godan
        stem = ChangeLastLetter(stem, 1) + "せる"
    Case emGroup.ichidan
        stem = Left(stem, Len(stem) - 1)
        stem = stem + "させる"
    Case emGroup.henkaku
        If Right(stem, 2) = "する" Then
            stem = Left(stem, Len(stem) - 2) + "させる"
        Else
        stem = Left(stem, Len(stem) - 1)
        stem = ChangeLastLetter(stem, 5) + "させる"
        End If
    End Select
    Jisho2Shieki = stem
End Function

Function Jisho2Ukemi(word As String, group As emGroup) As String
    Dim ret As String, stem As String
    stem = Trim(word)
    Select Case group
    Case emGroup.godan
        stem = ChangeLastLetter(stem, 1) + "れる"
    Case emGroup.ichidan
        stem = Left(stem, Len(stem) - 1)
        stem = stem + "られる"
    Case emGroup.henkaku
        If Right(stem, 2) = "する" Then
            stem = Left(stem, Len(stem) - 2) + "される"
        Else
        stem = Left(stem, Len(stem) - 1)
        stem = ChangeLastLetter(stem, 5) + "られる"
        End If
    End Select
    Jisho2Ukemi = stem
End Function

Function Jisho2Meirei(word As String, group As emGroup) As String
    Dim ret As String, stem As String
    stem = Trim(word)
    '「～しゃる」の処理
    If Right(stem, 3) = "しゃる" Then
        Jisho2Meirei = Left(stem, Len(stem) - 1) + "い"
        Exit Function
    End If

    Select Case group
    Case emGroup.godan
        stem = ChangeLastLetter(stem, 4)
    Case emGroup.ichidan
        stem = Left(stem, Len(stem) - 1)
        stem = stem + "ろ"
    Case emGroup.henkaku
        If Right(stem, 2) = "する" Then
            stem = Left(stem, Len(stem) - 2) + "しろ"
        Else
        stem = Left(stem, Len(stem) - 1)
        stem = ChangeLastLetter(stem, 5) + "い"
        End If
    End Select
    Jisho2Meirei = stem
End Function

Function Jisho2Kanou(word As String, group As emGroup) As String
    Dim ret As String, stem As String
    stem = Trim(word)
    Select Case group
    Case emGroup.godan
        stem = ChangeLastLetter(stem, 4) + "る"
    Case emGroup.ichidan
        stem = Left(stem, Len(stem) - 1)
        stem = stem + "られる"
    Case emGroup.henkaku
        If Right(stem, 2) = "する" Then
            stem = Left(stem, Len(stem) - 2) + "できる"
        Else
        stem = Left(stem, Len(stem) - 1)
        stem = ChangeLastLetter(stem, 5) + "れる"
        End If
    End Select
    Jisho2Kanou = stem
End Function

Function Jisho2Ikou(word As String, group As emGroup) As String
    Dim ret As String, stem As String
    stem = Trim(word)
    Select Case group
    Case emGroup.godan
        stem = ChangeLastLetter(stem, 5) + "う"
    Case emGroup.ichidan
        stem = Left(stem, Len(stem) - 1)
        stem = stem + "よう"
    Case emGroup.henkaku
        If Right(stem, 2) = "する" Then
            stem = Left(stem, Len(stem) - 2) + "しよう"
        Else
        stem = Left(stem, Len(stem) - 1)
        stem = ChangeLastLetter(stem, 5) + "よう"
        End If
    End Select
    Jisho2Ikou = stem
End Function

Function Jisho2Shuushi(word As String, group As emGroup) As String
    Dim stem As String
    stem = Trim(word)
    Select Case group
        Case emGroup.an
        stem = Left(stem, Len(stem) - 1) + "だ"
    End Select
    Jisho2Shuushi = stem
End Function

Function Jisho2Fukushi(word As String, group As emGroup) As String
    Dim stem As String
    stem = Trim(word)
    Select Case group
        Case emGroup.ai
        If (Len(stem) = 2 And stem = "いい") Or (Right(stem, 3) = "がいい") Then
            stem = Left(stem, Len(stem) - 2) + "よく"
        Else
            stem = Left(stem, Len(stem) - 1) + "く"
        End If
        Case emGroup.an
        stem = Left(stem, Len(stem) - 1) + "に"
    End Select
    Jisho2Fukushi = stem
End Function


Function ChangeLastLetter(word As String, retu As Integer) As String
    Dim table As Variant, lastletter As String, i As Integer, stem As String
    table = Array("わいうえお", "かきくけこ", "さしすせそ", "たちつてと", "なにぬねの", "はひふへほ", "まみむめも", "やいゆえよ", "らりるれろ", _
            "がぎぐげご", "ざじずぜぞ", "だぢづでど", "ばびぶべぼ", "ぱぴぷぺぽ", "あいうえお", "gate")
    ChangeLastLetter = ""
    lastletter = Right(word, 1)
    If isHiragana(lastletter) = False Then
        ChangeLastLetter = word
        Exit Function
    End If
    stem = Left(word, Len(word) - 1)
    For i = 0 To UBound(table)
        If InStr(table(i), lastletter) >= 1 Then Exit For
    Next i
    
    If i >= UBound(table) Then Exit Function
    stem = stem + Mid(table(i), retu, 1)
    ChangeLastLetter = stem
End Function

Function ChangeLastLetterwithOnbin(word As String, katuyou As emKatuyou) As String
    Dim table As Variant, onbin As Variant, dakuon As Variant, lastletter As String, i As Integer, stem As String
    table = Array("わいうえお", "かきくけこ", "さしすせそ", "たちつてと", "なにぬねの", "はひふへほ", "まみむめも", "やいゆえよ", "らりるれろ", _
            "がぎぐげご", "ざじずぜぞ", "だぢづでど", "ばびぶべぼ", "ぱぴぷぺぽ", "あいうえお", "gate")
    onbin = Array("っ", "い", "し", "っ", "ん", "う", "ん", "ゐ", "っ", "い", "じ", "じ", "ん", "ぴ", "っ", "")
    dakuon = Array(False, False, False, False, True, False, True, False, False, True, False, False, True, False, False, False)
    ChangeLastLetterwithOnbin = ""
    lastletter = Right(word, 1)
    stem = Left(word, Len(word) - 1)
    For i = 0 To UBound(table)
        If InStr(table(i), lastletter) >= 1 Then Exit For
    Next i
    Select Case katuyou
    Case emKatuyou.te
        If dakuon(i) = True Then
            ChangeLastLetterwithOnbin = stem + onbin(i) + "で"
        Else
            ChangeLastLetterwithOnbin = stem + onbin(i) + "て"
        End If
    Case emKatuyou.ta
        If dakuon(i) = True Then
            ChangeLastLetterwithOnbin = stem + onbin(i) + "だ"
        Else
            ChangeLastLetterwithOnbin = stem + onbin(i) + "た"
        End If
    End Select
End Function

Function isHiragana(chr As String) As Boolean
    If AscW(chr) >= &H3041 And AscW(chr) <= &H309F Then
        isHiragana = True
    Else
        isHiragana = False
    End If
End Function

