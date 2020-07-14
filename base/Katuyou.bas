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
            MsgBox "�����`"
        Case masu
            MsgBox "�܂��`"
        Case te
            MsgBox "�Č`"
        Case ta
            MsgBox "���`"
        Case nai
            MsgBox "�Ȃ��`"
        Case nakatta
            MsgBox "�Ȃ������`"
        Case ba
            MsgBox "�Ό`"
        Case shieki
            MsgBox "�g���`"
        Case ukemi
            MsgBox "��g�`"
        Case meirei
            MsgBox "���ߌ`"
        Case kanou
            MsgBox "�\�`"
        Case ikou
            MsgBox "�ӌ��`"
            
    End Select
    
End Sub



Public Sub showGroup(group As emGroup)
    Select Case group
    Case godan
        MsgBox "�ܒi���p"
    Case ichidan
        MsgBox "��i���p"
    Case henkaku
        MsgBox "�ϊi���p"
    Case ai
        MsgBox "�`�e��"
    Case an
        MsgBox "�`�e����"
    End Select
End Sub
Sub autoconv()

End Sub

Function Convert(str As String)
    
End Function

Function Masu2Jisho(word As String, group As emGroup) As String
    Dim ret As String, stem As String
    ret = Trim(word)
'�u�܂��v�̏���
    stem = Left(ret, Len(ret) - 2)
    Select Case group
    Case emGroup.godan
        stem = ChangeLastLetter(stem, 3)
    Case emGroup.ichidan
        stem = stem + "��"
    Case emGroup.henkaku
        If isHiragana(Right(stem, 1)) = True Then
        stem = ChangeLastLetter(stem, 3) + "��"
        Else
        stem = stem + "��"
        End If
    End Select
    Masu2Jisho = stem
End Function

Function Jisho2Masu(word As String, group As emGroup) As String
    Dim ret As String, stem As String
    stem = Trim(word)
'�u�`�����v�̏���
    If Right(stem, 3) = "�����" Then
        Jisho2Masu = Left(stem, Len(stem) - 1) + "���܂�"
        Exit Function
    End If
    Select Case group
    Case emGroup.godan
        stem = ChangeLastLetter(stem, 2) + "�܂�"
    Case emGroup.ichidan
        stem = Left(stem, Len(stem) - 1)
        stem = stem + "�܂�"
    Case emGroup.henkaku
        stem = Left(stem, Len(stem) - 1)
        stem = ChangeLastLetter(stem, 2) + "�܂�"
    Case emGroup.ai
        stem = stem + "�ł�"
    Case emGroup.an
        stem = Left(stem, Len(stem) - 1) + "�ł�"
    End Select
    Jisho2Masu = stem
End Function

Function Jisho2Te(word As String, group As emGroup) As String
    Dim ret As String, stem As String
    stem = Trim(word)
'�u�s���v�̏���
    ret = Right(stem, 2)
    If (ret = "����" Or ret = "�s��") And (group = emGroup.godan) Then
        Jisho2Te = Left(stem, Len(stem) - 1) + "����"
        Exit Function
    End If
    
    Select Case group
    Case emGroup.godan
        stem = ChangeLastLetterwithOnbin(stem, emKatuyou.te)
    Case emGroup.ichidan
        stem = Left(stem, Len(stem) - 1)
        stem = stem + "��"
    Case emGroup.henkaku
        If Right(stem, 2) = "����" Then
            stem = Left(stem, Len(stem) - 2) + "����"
        Else
            stem = ChangeLastLetter(Left(stem, Len(stem) - 1), 2) + "��"
        End If
    Case emGroup.ai
        If (Len(stem) = 2 And stem = "����") Or (Right(stem, 3) = "������") Then
            stem = Left(stem, Len(stem) - 2) + "�悭��"
        Else
            stem = Left(stem, Len(stem) - 1) + "����"
        End If
    Case emGroup.an
        stem = Left(stem, Len(stem) - 1) + "��"
    End Select
    Jisho2Te = stem
End Function

Function Jisho2Ta(word As String, group As emGroup) As String
    Dim ret As String, stem As String
    stem = Trim(word)
'�u�s���v�̏���
    ret = Right(stem, 2)
    If (ret = "����" Or ret = "�s��") And (group = emGroup.godan) Then
        Jisho2Ta = Left(stem, Len(stem) - 1) + "����"
        Exit Function
    End If
    Select Case group
    Case emGroup.godan
        stem = ChangeLastLetterwithOnbin(stem, emKatuyou.ta)
    Case emGroup.ichidan
        stem = Left(stem, Len(stem) - 1)
        stem = stem + "��"
    Case emGroup.henkaku
        If Right(stem, 2) = "����" Then
            stem = Left(stem, Len(stem) - 2) + "����"
        Else
            stem = ChangeLastLetter(Left(stem, Len(stem) - 1), 2) + "��"
        End If
    Case emGroup.ai
        If (Len(stem) = 2 And stem = "����") Or (Right(stem, 3) = "������") Then
            stem = Left(stem, Len(stem) - 2) + "�悩����"
        Else
            stem = Left(stem, Len(stem) - 1) + "������"
        End If
    Case emGroup.an
        stem = Left(stem, Len(stem) - 1) + "������"
    End Select
    Jisho2Ta = stem
End Function


Function Jisho2Nai(word As String, group As emGroup) As String
    Dim ret As String, stem As String
    stem = Trim(word)
    Select Case group
    Case emGroup.godan
        stem = ChangeLastLetter(stem, 1) + "�Ȃ�"
    Case emGroup.ichidan
        stem = Left(stem, Len(stem) - 1)
        stem = stem + "�Ȃ�"
    Case emGroup.henkaku
        If Right(stem, 2) = "����" Then
            stem = Left(stem, Len(stem) - 2) + "���Ȃ�"
        Else
        stem = Left(stem, Len(stem) - 1)
        stem = ChangeLastLetter(stem, 5) + "�Ȃ�"
        End If
    Case emGroup.ai
        If (Len(stem) = 2 And stem = "����") Or (Right(stem, 3) = "������") Then
            stem = Left(stem, Len(stem) - 2) + "�悭�Ȃ�"
        Else
            stem = Left(stem, Len(stem) - 1) + "���Ȃ�"
        End If
    Case emGroup.an
        stem = Left(stem, Len(stem) - 1) + "����Ȃ�"
    End Select
    Jisho2Nai = stem
End Function

Function Jisho2Nakatta(word As String, group As emGroup) As String
    Dim ret As String
    ret = Jisho2Nai(word, group)
    Jisho2Nakatta = Left(ret, Len(ret) - 1) + "������"
End Function

Function Jisho2ba(word As String, group As emGroup) As String
    Dim ret As String, stem As String
    stem = Trim(word)
    Select Case group
    Case emGroup.godan, emGroup.ichidan
        stem = ChangeLastLetter(stem, 4) + "��"
    Case emGroup.henkaku
        If Right(stem, 2) = "����" Then
            stem = Left(stem, Len(stem) - 2) + "�����"
        Else
        stem = Left(stem, Len(stem) - 1)
        stem = ChangeLastLetter(stem, 5) + "���"
        End If
    Case emGroup.ai
        If (Len(stem) = 2 And stem = "����") Or (Right(stem, 3) = "������") Then
            stem = Left(stem, Len(stem) - 2) + "�悯���"
        Else
            stem = Left(stem, Len(stem) - 1) + "�����"
        End If
    Case emGroup.an
        stem = Left(stem, Len(stem) - 1) + "�Ȃ��"
    End Select
    Jisho2ba = stem
End Function

Function Jisho2Shieki(word As String, group As emGroup) As String
    Dim ret As String, stem As String
    stem = Trim(word)
    Select Case group
    Case emGroup.godan
        stem = ChangeLastLetter(stem, 1) + "����"
    Case emGroup.ichidan
        stem = Left(stem, Len(stem) - 1)
        stem = stem + "������"
    Case emGroup.henkaku
        If Right(stem, 2) = "����" Then
            stem = Left(stem, Len(stem) - 2) + "������"
        Else
        stem = Left(stem, Len(stem) - 1)
        stem = ChangeLastLetter(stem, 5) + "������"
        End If
    End Select
    Jisho2Shieki = stem
End Function

Function Jisho2Ukemi(word As String, group As emGroup) As String
    Dim ret As String, stem As String
    stem = Trim(word)
    Select Case group
    Case emGroup.godan
        stem = ChangeLastLetter(stem, 1) + "���"
    Case emGroup.ichidan
        stem = Left(stem, Len(stem) - 1)
        stem = stem + "����"
    Case emGroup.henkaku
        If Right(stem, 2) = "����" Then
            stem = Left(stem, Len(stem) - 2) + "�����"
        Else
        stem = Left(stem, Len(stem) - 1)
        stem = ChangeLastLetter(stem, 5) + "����"
        End If
    End Select
    Jisho2Ukemi = stem
End Function

Function Jisho2Meirei(word As String, group As emGroup) As String
    Dim ret As String, stem As String
    stem = Trim(word)
    '�u�`�����v�̏���
    If Right(stem, 3) = "�����" Then
        Jisho2Meirei = Left(stem, Len(stem) - 1) + "��"
        Exit Function
    End If

    Select Case group
    Case emGroup.godan
        stem = ChangeLastLetter(stem, 4)
    Case emGroup.ichidan
        stem = Left(stem, Len(stem) - 1)
        stem = stem + "��"
    Case emGroup.henkaku
        If Right(stem, 2) = "����" Then
            stem = Left(stem, Len(stem) - 2) + "����"
        Else
        stem = Left(stem, Len(stem) - 1)
        stem = ChangeLastLetter(stem, 5) + "��"
        End If
    End Select
    Jisho2Meirei = stem
End Function

Function Jisho2Kanou(word As String, group As emGroup) As String
    Dim ret As String, stem As String
    stem = Trim(word)
    Select Case group
    Case emGroup.godan
        stem = ChangeLastLetter(stem, 4) + "��"
    Case emGroup.ichidan
        stem = Left(stem, Len(stem) - 1)
        stem = stem + "����"
    Case emGroup.henkaku
        If Right(stem, 2) = "����" Then
            stem = Left(stem, Len(stem) - 2) + "�ł���"
        Else
        stem = Left(stem, Len(stem) - 1)
        stem = ChangeLastLetter(stem, 5) + "���"
        End If
    End Select
    Jisho2Kanou = stem
End Function

Function Jisho2Ikou(word As String, group As emGroup) As String
    Dim ret As String, stem As String
    stem = Trim(word)
    Select Case group
    Case emGroup.godan
        stem = ChangeLastLetter(stem, 5) + "��"
    Case emGroup.ichidan
        stem = Left(stem, Len(stem) - 1)
        stem = stem + "�悤"
    Case emGroup.henkaku
        If Right(stem, 2) = "����" Then
            stem = Left(stem, Len(stem) - 2) + "���悤"
        Else
        stem = Left(stem, Len(stem) - 1)
        stem = ChangeLastLetter(stem, 5) + "�悤"
        End If
    End Select
    Jisho2Ikou = stem
End Function

Function Jisho2Shuushi(word As String, group As emGroup) As String
    Dim stem As String
    stem = Trim(word)
    Select Case group
        Case emGroup.an
        stem = Left(stem, Len(stem) - 1) + "��"
    End Select
    Jisho2Shuushi = stem
End Function

Function Jisho2Fukushi(word As String, group As emGroup) As String
    Dim stem As String
    stem = Trim(word)
    Select Case group
        Case emGroup.ai
        If (Len(stem) = 2 And stem = "����") Or (Right(stem, 3) = "������") Then
            stem = Left(stem, Len(stem) - 2) + "�悭"
        Else
            stem = Left(stem, Len(stem) - 1) + "��"
        End If
        Case emGroup.an
        stem = Left(stem, Len(stem) - 1) + "��"
    End Select
    Jisho2Fukushi = stem
End Function


Function ChangeLastLetter(word As String, retu As Integer) As String
    Dim table As Variant, lastletter As String, i As Integer, stem As String
    table = Array("�킢������", "����������", "����������", "�����Ă�", "�Ȃɂʂ˂�", "�͂Ђӂւ�", "�܂݂ނ߂�", "�₢�䂦��", "������", _
            "����������", "����������", "�����Âł�", "�΂тԂׂ�", "�ς҂Ղ؂�", "����������", "gate")
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
    table = Array("�킢������", "����������", "����������", "�����Ă�", "�Ȃɂʂ˂�", "�͂Ђӂւ�", "�܂݂ނ߂�", "�₢�䂦��", "������", _
            "����������", "����������", "�����Âł�", "�΂тԂׂ�", "�ς҂Ղ؂�", "����������", "gate")
    onbin = Array("��", "��", "��", "��", "��", "��", "��", "��", "��", "��", "��", "��", "��", "��", "��", "")
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
            ChangeLastLetterwithOnbin = stem + onbin(i) + "��"
        Else
            ChangeLastLetterwithOnbin = stem + onbin(i) + "��"
        End If
    Case emKatuyou.ta
        If dakuon(i) = True Then
            ChangeLastLetterwithOnbin = stem + onbin(i) + "��"
        Else
            ChangeLastLetterwithOnbin = stem + onbin(i) + "��"
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

