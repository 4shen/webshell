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

Imports System.Text

Public Class HttpParser


#Region "Enums"

    ''' <summary>
    ''' HTTP Client Methods
    ''' </summary>
    ''' <remarks></remarks>
    Public Enum HTTPMethod
        [GET]
        POST
        CONNECT
        HEAD
        TRACE
        PUT
        DELETE
        OPTIONS
        LINK
        UNLINK
        PATCH
    End Enum

#End Region

#Region "Properties"

    Private _Method As HTTPMethod

    ''' <summary>
    ''' HTTP Method
    ''' </summary>
    ''' <value></value>
    ''' <returns></returns>
    ''' <remarks></remarks>
    Public Property Method() As HTTPMethod
        Get
            Return _Method
        End Get
        Set(ByVal value As HTTPMethod)
            _Method = value
        End Set
    End Property

    Private _Version As String
    ''' <summary>
    ''' HTTP Version
    ''' </summary>
    ''' <value></value>
    ''' <returns></returns>
    ''' <remarks></remarks>
    Public Property Version() As String
        Get
            Return _Version
        End Get
        Set(ByVal value As String)
            _Version = value
        End Set
    End Property

    Private _ParseAsResponsense As Boolean
    ''' <summary>
    ''' Parse as HTTP Response 
    ''' </summary>
    ''' <value></value>
    ''' <returns></returns>
    ''' <remarks></remarks>
    Public Property ParseAsResponse() As Boolean
        Get
            Return _ParseAsResponsense
        End Get
        Set(ByVal value As Boolean)
            _ParseAsResponsense = value
        End Set
    End Property


    Private _HttpResponse As HttpResponse
    ''' <summary>
    ''' HTTP Response
    ''' </summary>
    ''' <value></value>
    ''' <returns></returns>
    ''' <remarks></remarks>
    Public Property HttpResponse() As HttpResponse
        Get
            If _HttpResponse Is Nothing Then _HttpResponse = New HttpResponse()
            Return _HttpResponse
        End Get
        Set(ByVal value As HttpResponse)
            _HttpResponse = value
        End Set
    End Property


    Private _Url As Uri
    ''' <summary>
    ''' Requested Path
    ''' </summary>
    ''' <value></value>
    ''' <returns></returns>
    ''' <remarks></remarks>
    Public Property Url() As Uri
        Get
            Return _Url
        End Get
        Set(ByVal value As Uri)
            _Url = value
        End Set
    End Property

#End Region

#Region "Constructor"

    ''' <summary>
    ''' New HTTP Request Parser
    ''' </summary>
    ''' <param name="request">Request</param>
    ''' <remarks></remarks>
    Public Sub New(ByVal request As String)
        ParseRequest(request)
    End Sub


    ''' <summary>
    ''' New HTTP Response Parser
    ''' </summary>
    ''' <param name="request">Request</param>
    ''' <remarks></remarks>
    Public Sub New(ByVal request As String, ByVal body As Byte())
        Me.ParseAsResponse = True
        Me.HttpResponse.Body = body
        ParseRequest(request)
    End Sub

    ''' <summary>
    ''' New HTTP Request from buffer
    ''' </summary>
    ''' <param name="request">HTTP Request Buffer</param>
    ''' <param name="readData">Bytes to read</param>
    ''' <remarks></remarks>
    Public Sub New(ByVal request() As Byte, ByVal readData As Integer)
        Dim RequestString As String = Encoding.ASCII.GetString(request, 0, readData)
        ParseRequest(RequestString)
    End Sub


    ''' <summary>
    ''' Parse HTTP Request
    ''' </summary>
    ''' <param name="request"></param>
    ''' <remarks></remarks>
    Private Sub ParseRequest(ByVal request As String)
        ParseQuery(request)
    End Sub

#End Region

    Private _RequestBody As String = String.Empty

    ''' <summary>
    ''' Request Body (generally post)
    ''' </summary>
    ''' <value></value>
    ''' <returns></returns>
    ''' <remarks></remarks>
    Public Property RequestBody() As String
        Get
            Return _RequestBody
        End Get
        Set(ByVal value As String)
            _RequestBody = value
        End Set
    End Property



    ''' <summary>
    ''' Parse HTTP Request or Response
    ''' </summary>
    ''' <param name="request"></param>
    ''' <remarks></remarks>
    Private Sub ParseQuery(ByVal request As String)

        If Not Me.ParseAsResponse Then
            Dim BodyPos As Integer = request.IndexOf(vbNewLine & vbNewLine)
            If BodyPos > -1 Then
                Me.RequestBody = request.Substring(BodyPos + 4, request.Length - (BodyPos + 4))
            End If
        End If

        request = request.Replace(vbNewLine, Chr(13))
        Dim Lines() As String = request.Split(Chr(13))

        Dim Cnt, Ret As Integer

        If Lines.Length > 0 Then
            Ret = Lines(0).IndexOf(" ")

            If (Ret > 0) Then

                Dim FirstPart As String = Lines(0).Substring(0, Ret)
                If Me.ParseAsResponse Then
                    If Not Integer.TryParse(FirstPart, Me.HttpResponse.Status) Then
                        'Status Parse Error !
                        Debug.WriteLine("ERR : Status not integer!")
                        Me.HttpResponse.Status = 999
                    End If

                    Me.HttpResponse.StatusText = Lines(0).Substring(Ret).Trim()

                Else
                    Me.Method = CType(System.Enum.Parse(GetType(HTTPMethod), FirstPart), HTTPMethod)

                End If

                Lines(0) = Lines(0).Substring(Ret).Trim()

            End If

            'If Parsing HTTP Request, Get requested URI
            If Not Me.ParseAsResponse Then
                'Parse the Http Version and the Requested Path
                Dim Path As String
                Ret = Lines(0).LastIndexOf(" ")
                If (Ret > 0) Then
                    Me.Version = Lines(0).Substring(Ret).Trim()
                    Path = Lines(0).Substring(0, Ret)

                Else
                    Path = Lines(0)

                End If

                CreateUri(Path)
            End If

        End If

        'Generate Headers
        For Cnt = 1 To Lines.Length - 1

            Ret = Lines(Cnt).IndexOf(":")
            If (Ret > 0 AndAlso Ret < Lines(Cnt).Length - 1) Then

                Try
                    Dim HeaderName, HeaderValue As String
                    HeaderName = Lines(Cnt).Substring(0, Ret)
                    HeaderValue = Lines(Cnt).Substring(Ret + 1).Trim()

                    Me.HttpResponse.AddHeader(HeaderName, HeaderValue)

                Catch ex As Exception
                    Debug.WriteLine("Bad header response...")

                End Try
            End If

        Next Cnt

    End Sub


    ''' <summary>
    ''' Create Uri
    ''' </summary>
    ''' <remarks></remarks>
    Private Sub CreateUri(ByVal Path As String)

        If (Path.Length >= 7 AndAlso Path.Substring(0, 7).ToLower().Equals("http://")) Then

            Try
                Url = New Uri(Path)

            Catch ex As Exception
                Console.WriteLine("Uri Error :" & ex.ToString)

            End Try

        End If

    End Sub

    ''' <summary>
    ''' Decode Binary from XSS Shell Server
    ''' </summary>
    ''' <param name="input">Encoded string to decode</param>
    ''' <returns></returns>
    ''' <remarks></remarks>
    Public Shared Function DecodeBinary(ByVal input As String) As Byte()
        If (input.Length Mod 2) <> 0 Then
            'Wrong Binary Data
            Return Nothing
        End If

        Dim RetBuffer(CInt(input.Length / 2) - 1) As Byte
        For i As Integer = 0 To RetBuffer.Length - 1
            RetBuffer(i) = Convert.ToByte(Integer.Parse(input.Chars((i * 2)) & input.Chars((i * 2) + 1), Globalization.NumberStyles.HexNumber))
        Next i

        Return RetBuffer
    End Function

End Class
