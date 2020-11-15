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
Imports System.IO
Imports System.Collections
Imports System.Web
Imports System.Text

''' <summary>
''' Make Request and fire up related events
''' </summary>
Friend Class HttpReader
#Region "Web Request"

    Private _WebRequest As HttpWebRequest
    ''' <summary>
    ''' Web Request 
    ''' </summary>
    ''' <value></value>
    ''' <returns></returns>
    ''' <remarks></remarks>
    Public Property WebRequest() As HttpWebRequest
        Get
            If _WebRequest Is Nothing Then _WebRequest = GetWebRequest(Me.Uri)
            Return _WebRequest
        End Get
        Set(ByVal value As HttpWebRequest)
            _WebRequest = value
        End Set
    End Property

    Private _Uri As Uri
    ''' <summary>
    ''' Uri to Request
    ''' </summary>
    ''' <value></value>
    ''' <returns></returns>
    ''' <remarks></remarks>
    Public Property Uri() As Uri
        Get
            Return _Uri
        End Get
        Set(ByVal value As Uri)
            _Uri = value
        End Set
    End Property




#End Region

#Region "Enums"
    ''' <summary>
    ''' Http Methods
    ''' </summary>
    ''' <remarks></remarks>
    Public Enum HttpMethod
        [Get] = 0 'Default Method
        Post
        Head
        Put
        Trace
        Options
        Delete
        PostMultiPart
    End Enum

#End Region

#Region "Constructors"
    ''' <summary>
    ''' New HttpRequest
    ''' </summary>
    ''' <param name="uri"></param>
    ''' <remarks></remarks>
    Public Sub New(ByVal uri As Uri)
        Me.Uri = uri
    End Sub

#End Region

    Private _PostData As String
    Public Property PostData() As String
        Get
            Return _PostData
        End Get
        Set(ByVal value As String)
            _PostData = value
        End Set
    End Property


    Private _Settings As UriManager
    Public Property Settings() As UriManager
        Get
            If _Settings Is Nothing Then _Settings = New UriManager()
            Return _Settings
        End Get
        Set(ByVal value As UriManager)
            _Settings = value
        End Set
    End Property


#Region "Main Methods"

    ''' <summary>
    ''' Make a request
    ''' </summary>
    ''' <remarks></remarks>
    Public Function Request() As String
        Dim WebReq As HttpWebRequest = Me.WebRequest

        'Check WebRequest
        If WebReq Is Nothing Then
            Debug.Assert(False, "Request is nothing ?")
            Return Nothing
        End If


        'Exit by default in exception
        Dim ExitFunction As Boolean = True

        Dim HttpRes As HttpWebResponse = Nothing
        Dim requestTime As DateTime = DateTime.UtcNow
        Dim SourceCode As String = String.Empty

        Try
            HttpRes = CType(WebReq.GetResponse(), HttpWebResponse)
            ExitFunction = False

        Catch WebEx As Net.WebException 'Handle Web Exceptions

            Select Case WebEx.Status
                Case WebExceptionStatus.Timeout
                    ExitFunction = True

                Case WebExceptionStatus.ProtocolError 'Status 500, 401 etc.
                    HttpRes = DirectCast(WebEx.Response, HttpWebResponse)

                Case WebExceptionStatus.ConnectFailure

                Case WebExceptionStatus.TrustFailure
                    ExitFunction = True

                Case WebExceptionStatus.ServerProtocolViolation
                    ExitFunction = True

                Case Else

                    Debug.WriteLine(WebEx.Message)
                    ExitFunction = True

            End Select

        Catch Ex As InvalidOperationException
            ExitFunction = True


        Finally 'WebException

            Try
                If ExitFunction Then Exit Try

                Debug.Assert(Not HttpRes Is Nothing, "We shouldn't be in here if Response is nothing !")

                Try
                    'Read stream into source code
                    'TODO3 : Limit response size
                    Using SReader As StreamReader = New StreamReader(HttpRes.GetResponseStream)
                        SourceCode = SReader.ReadToEnd
                    End Using

                Catch ex As Exception

                    'Stream Read Error etc. Summary: Fucked up!
                    ExitFunction = True

                End Try

            Catch ex As Exception
                Debug.WriteLine(ex.ToString)

            Finally
                If HttpRes IsNot Nothing Then HttpRes.Close()

            End Try


        End Try 'Webexception


        'Just leave if we need to exit
        If ExitFunction Then Return Nothing

        'Debug.WriteLine("--------Source Code--------")
        'Debug.WriteLine(SourceCode)
        'Debug.WriteLine("---------------------------")

        Return SourceCode
    End Function

#End Region


    Public Function GetWebRequest(ByVal Uri As Uri) As HttpWebRequest
        Dim WebReq As HttpWebRequest

        Try
            WebReq = CType(HttpWebRequest.Create(Uri), HttpWebRequest)

        Catch ex As NotSupportedException
            Debug.Assert(False, "Fix it at analyzpage()")
            Return Nothing

        End Try

        'Do not follow redirect automaticly we'll do it manually
        WebReq.AllowAutoRedirect = False

        'Add Credentials
        If Me.Settings.Credential IsNot Nothing Then
            WebReq.Credentials = Me.Settings.Credential
        End If

        'Check Proxy
        If Me.Settings.ProxyEnabled Then

            'If there is proxy use otherwise use IE proxy
            If Me.Settings.Proxy IsNot Nothing Then
                WebReq.Proxy = Me.Settings.Proxy
            End If

        Else
            WebReq.Proxy = Nothing

        End If

        'Add Browser
        WebReq.Accept = Me.Settings.Browser.Accept
        WebReq.ContentType = Me.Settings.Browser.ContentType
        WebReq.UserAgent = Me.Settings.Browser.UserAgent


        'Add Headers
        For Each Header As KeyValuePair(Of String, String) In Settings.Headers
            WebReq.Headers.Add(Header.Key, Header.Value)
        Next Header


        'Application should follow redirect
        'Should give up after a max. try
        'Should not add if it's external or out of scan scope (folder, filetype etc.)
        'For now just do not follow...
        WebReq.AllowAutoRedirect = False


        'Cache Control - Do not cache
        WebReq.Headers.Add(HttpRequestHeader.CacheControl, "no-cache")

        'Get Cookies (custom or/and shared session)
        WebReq.CookieContainer = Settings.CookieContainer

        'Request Timeout
        WebReq.Timeout = Settings.RequestTimeout

        'Choose Post Method
        If PostData IsNot Nothing AndAlso PostData <> String.Empty Then
            WebReq.Method = HttpParser.HTTPMethod.POST.ToString

            WebReq.ContentType = "application/x-www-form-urlencoded"
            WebReq.ContentLength = PostData.Length

            Dim ReqStream As Stream = Nothing
            Try
                ReqStream = WebReq.GetRequestStream()
                Dim PostByte As Byte() = Encoding.ASCII.GetBytes(PostData)
                ReqStream.Write(PostByte, 0, PostByte.Length)

            Catch WebEx As Net.WebException
                Debug.WriteLine(WebEx.ToString & "Form GetRequestStream() Error !")

            Finally
                If Not ReqStream Is Nothing Then ReqStream.Close()

            End Try

        Else
            WebReq.Method = HttpParser.HTTPMethod.GET.ToString

        End If

        Return WebReq
    End Function

End Class

