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

#Region "UriManager"
''' <summary>
''' UriManager Settings
''' </summary>
''' <remarks></remarks>
Public Class UriManager
    ''' <summary>
    ''' Lock Object for thread-safety
    ''' </summary>
    ''' <remarks></remarks>
    Private ObjLock As New Object

    Private _Proxy As WebProxy
    ''' <summary>
    ''' Proxy Settings
    ''' </summary>
    ''' <value>The proxy.</value>
    ''' <returns></returns>
    ''' <remarks></remarks>
    Public Property Proxy() As WebProxy
        Get
            Return _Proxy
        End Get
        Set(ByVal value As WebProxy)
            _Proxy = value
        End Set
    End Property


    Private _proxyEnabled As Boolean

    ''' <summary>
    ''' Gets or sets a value indicating whether Proxy Enabled ?.
    ''' </summary>
    ''' <value><c>true</c> if Proxy Enabled; otherwise, <c>false</c>.</value>
    Public Property ProxyEnabled() As Boolean
        Get
            Return _proxyEnabled
        End Get
        Set(ByVal value As Boolean)
            _proxyEnabled = value
        End Set
    End Property

    Private _userAgentString As String

    ''' <summary>
    ''' Gets or sets the user agent string.
    ''' </summary>
    ''' <value>The user agent string.</value>
    Public Property UserAgentString() As String
        Get
            Return _userAgentString
        End Get
        Set(ByVal value As String)
            _userAgentString = value
        End Set
    End Property

    Private _Credential As NetworkCredential
    ''' <summary>
    ''' Gets or sets Network Credentials
    ''' </summary>
    ''' <value>The Network Credential.</value>
    ''' <returns></returns>
    ''' <remarks></remarks>
    Public Property Credential() As NetworkCredential
        Get
            Return _Credential
        End Get
        Set(ByVal value As NetworkCredential)
            _Credential = value
        End Set
    End Property

    Private _browser As Browser

    ''' <summary>
    ''' Gets or sets the browser.
    ''' </summary>
    ''' <value>The browser.</value>
    Public Property Browser() As Browser
        Get

            'If empty Load new one with defaults
            If _browser = Nothing Then
                _browser = New Browser()
                _browser.UserAgent = "XSS Shell"
                _browser.KeepAlive = True
            End If

            Return _browser
        End Get
        Set(ByVal value As Browser)
            _browser = value
        End Set
    End Property

    ''' <summary>
    ''' Add new cookie if it's not in already and not expired
    ''' </summary>
    ''' <param name="OldCookieCollection">The cookie.</param>
    ''' <param name="AddCookieCollection">The add cookie collection.</param>
    ''' <returns>New combined cookie collection</returns>
    ''' <remarks>Synch cookies, remove expired ones.</remarks>
    Private Shared Function SyncCookies(ByVal oldCookieCollection As CookieCollection, ByVal addCookieCollection As CookieCollection) As CookieCollection
        Dim RetCollection As New CookieCollection()

        'Combine Old Cookies and New ones
        oldCookieCollection.Add(addCookieCollection)

        'Do not add 
        For Each OldCookie As Cookie In oldCookieCollection
            'Add if session cookie or not expired
            If OldCookie.Expires = Date.MinValue OrElse Not OldCookie.Expired Then
                RetCollection.Add(OldCookie)
            End If
        Next

        Return RetCollection
    End Function

    Private _Cookies As CookieCollection
    ''' <summary>
    ''' Gets or sets the active cookies.
    ''' </summary>
    ''' <value>The active cookies.</value>
    Public Property Cookies() As CookieCollection
        Get
            SyncLock ObjLock
                If _Cookies Is Nothing Then _Cookies = New CookieCollection()

                'Add Custom Cookies
                _Cookies.Add(CustomCookies)

                Return _Cookies
            End SyncLock
        End Get
        Set(ByVal value As CookieCollection)
            SyncLock ObjLock
                _Cookies = SyncCookies(_Cookies, value)
            End SyncLock
        End Set
    End Property

    Private _CookieContainer As CookieContainer
    ''' <summary>
    ''' Gets or sets the cookie container.
    ''' </summary>
    ''' <value>The cookie container.</value>
    ''' <remarks>Returns a new cookie container for HttpWebRequests</remarks>
    Public ReadOnly Property CookieContainer() As CookieContainer
        Get
            If _CookieContainer Is Nothing Then
                _CookieContainer = New CookieContainer()
                _CookieContainer.Add(Cookies)
            End If

            'Accepted by Unit tests for now, seems OK
            '_CookieContainer = New CookieContainer()
            '_CookieContainer.Add(Cookies)

            Return _CookieContainer
        End Get
    End Property

    Private _RequestTimeout As Integer
    ''' <summary>
    ''' Gets or sets the request timeout as miliseconds.
    ''' </summary>
    ''' <value>The request timeout.</value>
    Public Property RequestTimeout() As Integer
        Get
            If _RequestTimeout = 0 Then _RequestTimeout = DefaultRequestTimeout
            Return _RequestTimeout
        End Get
        Set(ByVal value As Integer)
            _RequestTimeout = value
        End Set
    End Property


    Private _maximumRedirect As Integer

    ''' <summary>
    ''' Gets or sets the maximum redirect.
    ''' </summary>
    ''' <value>The maximum redirect.</value>
    Private ReadOnly Property DefaultMaximumRedirect() As Integer
        Get
            Return 3
        End Get
    End Property

    ''' <summary>
    ''' Gets or sets the maximum redirect.
    ''' </summary>
    ''' <value>The maximum redirect.</value>
    Public Property MaximumRedirect() As Integer
        Get
            If _maximumRedirect = 0 Then _maximumRedirect = DefaultMaximumRedirect
            Return _maximumRedirect
        End Get
        Set(ByVal value As Integer)
            _maximumRedirect = value
        End Set
    End Property

    ''' <summary>
    ''' Gets the default request timeout.
    ''' </summary>
    ''' <value>The default request timeout.</value>
    Public Shared ReadOnly Property DefaultRequestTimeout() As Integer
        Get
            Return 60000
        End Get
    End Property


    ''' <summary>
    ''' New UriManager Settings with default values
    ''' </summary>
    ''' <remarks></remarks>
    Public Sub New()

    End Sub


    Private _Headers As Dictionary(Of String, String)
    ''' <summary>
    ''' Gets or sets the Http Headers.
    ''' </summary>
    ''' <value>The Http Header.</value>
    Public Property Headers() As Dictionary(Of String, String)
        Get
            If _Headers Is Nothing Then _Headers = New Dictionary(Of String, String)
            Return _Headers
        End Get
        Set(ByVal value As Dictionary(Of String, String))
            _Headers = value
        End Set
    End Property

    ''' <summary>
    ''' Adds the header.
    ''' </summary>
    ''' <param name="Name">The name.</param>
    ''' <param name="Value">The value.</param>
    ''' <returns></returns>
    Public Function AddHeader(ByVal name As String, ByVal value As String) As Boolean
        If Headers.ContainsKey(name) Then
            Return False

        Else
            Headers.Add(name, value)
            Return True

        End If

    End Function


    Private _CustomCookies As New CookieCollection()
    ''' <summary>
    ''' Gets or sets the custom cookies.
    ''' </summary>
    ''' <value>The custom cookies.</value>
    ''' <remarks>Will replace value with old custom if already exist.</remarks>
    Public Property CustomCookies() As CookieCollection
        Get
            Return _CustomCookies
        End Get
        Set(ByVal value As CookieCollection)
            _CustomCookies = value
        End Set
    End Property

    Private _UseDefaultCredentials As Boolean
    ''' <summary>
    ''' Gets or sets a value indicating whether request should use default credentials.
    ''' </summary>
    ''' <value>
    ''' <c>true</c> if request uses default credentials; otherwise, <c>false</c>.
    ''' </value>
    ''' <remarks>Current application logged user credentials </remarks>
    Public Property UseDefaultCredentials() As Boolean
        Get
            Return _UseDefaultCredentials
        End Get
        Set(ByVal value As Boolean)
            _UseDefaultCredentials = value
        End Set
    End Property


End Class

#End Region
