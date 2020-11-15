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

''' <summary>
''' HTTP Response 
''' </summary>
''' <remarks></remarks>
Public Class HttpResponse

#Region "Constants"

    Private Const HTTPEndLine As String = vbNewLine & vbNewLine

    ''' <summary>
    ''' Failed Response Body
    ''' </summary>
    ''' <remarks></remarks>
    Private Const FailedResponse As String = "<html><head><title>XSS Tunnel Failed!</title></head><body><div style=""text-align:center""><strong>XSS Tunnel</strong> failed to get request. <br />You can refresh page and retry to do request.</div></body></html>"


#End Region

#Region "Properties"

    Private _Cached As Boolean
    ''' <summary>
    ''' Is Response coming from cache
    ''' </summary>
    ''' <value></value>
    ''' <returns></returns>
    ''' <remarks></remarks>
    Public Property Cached() As Boolean
        Get
            Return _Cached
        End Get
        Set(ByVal value As Boolean)
            _Cached = value
        End Set
    End Property


    Private _Status As Integer
    ''' <summary>
    ''' Status
    ''' </summary>
    ''' <value></value>
    ''' <returns></returns>
    ''' <remarks></remarks>
    Public Property Status() As Integer
        Get
            Return _Status
        End Get
        Set(ByVal value As Integer)
            _Status = value
        End Set
    End Property


    Private _StatusText As String
    ''' <summary>
    ''' HTTP Status Text
    ''' </summary>
    ''' <value></value>
    ''' <returns></returns>
    ''' <remarks></remarks>
    Public Property StatusText() As String
        Get
            Return _StatusText
        End Get
        Set(ByVal value As String)
            _StatusText = value
        End Set
    End Property


    Private _Headers As Dictionary(Of String, String)
    ''' <summary>
    ''' HTTP Headers
    ''' </summary>
    ''' <value></value>
    ''' <returns></returns>
    ''' <remarks></remarks>
    Public ReadOnly Property Headers() As Dictionary(Of String, String)
        Get
            If _Headers Is Nothing Then _Headers = New Dictionary(Of String, String)
            Return _Headers
        End Get
    End Property


    Private _Body() As Byte
    ''' <summary>
    ''' Response Body (decoded)
    ''' </summary>
    ''' <value></value>
    ''' <returns></returns>
    ''' <remarks></remarks>
    Public Property Body() As Byte()
        Get
            If _Body Is Nothing Then _Body = New Byte() {0}
            Return _Body
        End Get
        Set(ByVal value As Byte())
            _Body = value
        End Set
    End Property

#End Region

#Region "Generate Response"


    ''' <summary>
    ''' Get as valid HTTP Response Buffer (ready to send)
    ''' </summary>
    ''' <value></value>
    ''' <returns></returns>
    ''' <remarks></remarks>
    Public ReadOnly Property GetServerResponse() As Byte()
        Get

            Dim ResponseHeaderBuffer() As Byte = Text.Encoding.UTF8.GetBytes(ServerResponseHeaders)

            'Add content as byte and return
            Dim ResponseBuffer(ResponseHeaderBuffer.Length + Body.Length) As Byte
            Array.Copy(ResponseHeaderBuffer, 0, ResponseBuffer, 0, ResponseHeaderBuffer.Length)
            Array.Copy(Body, 0, ResponseBuffer, ResponseHeaderBuffer.Length, Body.Length)

            Return ResponseBuffer
        End Get
    End Property


    ''' <summary>
    ''' Generate Headers
    ''' </summary>
    ''' <value></value>
    ''' <returns></returns>
    ''' <remarks></remarks>
    Private ReadOnly Property ServerResponseHeaders() As String
        Get
            Dim RetHeaders As New Text.StringBuilder(500)

            RetHeaders.AppendLine("HTTP/1.1 " & Me.Status.ToString & " " & Me.StatusText)

            'Add Custom Headers
            Try
                Me.Headers.Add("Via-Server", "XSS Tunnel")
                If Not Body Is Nothing Then Me.Headers.Add("Content-Length", Body.Length.ToString)

            Catch ex As System.ArgumentException
            End Try


            For Each Header As KeyValuePair(Of String, String) In Me.Headers
                RetHeaders.AppendLine(Header.Key & ": " & Header.Value)
            Next Header

            'Double Newline
            RetHeaders.AppendLine()

            Debug.WriteLine("Response to Browser - Headers Only")
            Debug.WriteLine("====================")
            Debug.WriteLine(RetHeaders.ToString)
            Debug.WriteLine("====================")

            Return RetHeaders.ToString
        End Get
    End Property


    Dim HeaderLocker As New Object

    ''' <summary>
    ''' Add new HTTP Header
    ''' </summary>
    ''' <param name="key"></param>
    ''' <param name="value"></param>
    ''' <remarks></remarks>
    Public Sub AddHeader(ByVal key As String, ByVal value As String)
        SyncLock HeaderLocker

            If Not Me.Headers.ContainsKey(key) Then
                If CheckHeader(key) AndAlso CheckHeader(value) Then


                    'Choose which headers to add
                    'We hardcode here for accetable keys to make this process a bit secure
                    Select Case key
                        Case "Content-Length", "Via-Server", "Transfer-Encoding"
                            'Never accept these
                            'Transfer-Encoding:

                        Case Else
                            Me.Headers.Add(key, value)
                    End Select

                End If
            End If

        End SyncLock
    End Sub


    ''' <summary>
    ''' Check Value for potential injections
    ''' </summary>
    ''' <param name="value"></param>
    ''' <returns></returns>
    ''' <remarks>
    ''' This is still not safe, because of victim virtually can inject any header so we are totally open to http spltting variations. Eventually it's safe because we are running under victim's credentials so victim will hack himself (in theory). Still not perfect.
    ''' </remarks>
    Private Function CheckHeader(ByVal value As String) As Boolean

        If value.IndexOf(Chr(10)) > -1 OrElse value.IndexOf(Chr(13)) > -1 Then
            Return False
        End If

        Return True
    End Function

#End Region

End Class
