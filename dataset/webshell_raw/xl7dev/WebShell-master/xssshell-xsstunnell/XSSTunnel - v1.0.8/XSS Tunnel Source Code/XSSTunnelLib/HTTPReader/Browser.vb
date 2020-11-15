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
''' Browser Structure
''' </summary>
''' <remarks></remarks>
''' <history>
''' 15/10/2006
''' Started
''' Listening : Fisher Spooner - Never Mind (damn good !, the point is struggle and security)
''' </history>
Public Structure Browser

    Private _userAgent As String

    ''' <summary>
    ''' Gets or sets the user agent.
    ''' </summary>
    ''' <value>The user agent.</value>
    Public Property UserAgent() As String
        Get
            Return _userAgent
        End Get
        Set(ByVal value As String)
            _userAgent = value
        End Set
    End Property

    Private _contentType As String

    ''' <summary>
    ''' Gets or sets the type of the content.
    ''' </summary>
    ''' <value>The type of the content.</value>
    Public Property ContentType() As String
        Get
            Return _contentType
        End Get
        Set(ByVal value As String)
            _contentType = value
        End Set
    End Property

    Private _accept As String

    ''' <summary>
    ''' Gets or sets the accept.
    ''' </summary>
    ''' <value>The accept.</value>
    Public Property Accept() As String
        Get
            Return _accept
        End Get
        Set(ByVal value As String)
            _accept = value
        End Set
    End Property

    Private _acceptCharset As String
    ''' <summary>
    ''' Gets or sets the accept charset.
    ''' </summary>
    ''' <value>The accept charset.</value>
    Public Property AcceptCharset() As String
        Get
            Return _acceptCharset
        End Get
        Set(ByVal value As String)
            _acceptCharset = value
        End Set
    End Property

    Private _keepAlive As Boolean
    ''' <summary>
    ''' Gets or sets the keep alive.
    ''' </summary>
    ''' <value>The keep alive.</value>
    Public Property KeepAlive() As Boolean
        Get
            Return _keepAlive
        End Get
        Set(ByVal value As Boolean)
            _keepAlive = value
        End Set
    End Property

    Private _acceptLanguage As String

    ''' <summary>
    ''' Gets or sets the accept language.
    ''' </summary>
    ''' <value>The accept language.</value>
    Public Property AcceptLanguage() As String
        Get
            Return _acceptLanguage
        End Get
        Set(ByVal value As String)
            _acceptLanguage = value
        End Set
    End Property

    ''' <summary>
    ''' Compare objects
    ''' </summary>
    Public Shared Operator =(ByVal left As Browser, ByVal right As Browser) As Boolean
        Return left.UserAgent = right.UserAgent
    End Operator

    ''' <summary>
    ''' Compare objects
    ''' </summary>
    Public Shared Operator <>(ByVal left As Browser, ByVal right As Browser) As Boolean
        Return left.UserAgent <> right.UserAgent
    End Operator

    ''' <summary>
    ''' Add browser related strings to web request
    ''' </summary>
    ''' <param name="webRequest">WebRequest to be modified</param>
    Public Sub AddBrowserStrings(ByRef webRequest As Net.HttpWebRequest)
        If webRequest Is Nothing Then Return

        With webRequest
            .UserAgent = UserAgent

        End With

    End Sub



    ''' <summary>
    ''' Initializes a new instance of the <see cref="Browser" /> class.
    ''' </summary>
    ''' <param name="userAgent">The user agent.</param>
    ''' <param name="contentType">Type of the content.</param>
    ''' <param name="accept">The accept.</param>
    ''' <param name="acceptCharset">The accept charset.</param>
    ''' <param name="acceptLanguage">The accept language.</param>
    ''' <param name="keepAlive">The keep alive.</param>
    Public Sub New(ByVal userAgent As String, ByVal contentType As String, ByVal accept As String, ByVal acceptCharset As String, ByVal acceptLanguage As String, ByVal keepAlive As Boolean)
        Me.UserAgent = userAgent
        Me.ContentType = contentType
        Me.Accept = accept
        Me.AcceptCharset = acceptCharset
        Me.AcceptLanguage = acceptLanguage
        Me.KeepAlive = keepAlive
    End Sub

End Structure

