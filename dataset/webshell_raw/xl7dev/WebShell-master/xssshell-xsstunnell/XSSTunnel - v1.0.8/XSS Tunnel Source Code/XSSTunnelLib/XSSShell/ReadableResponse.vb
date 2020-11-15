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
''' Readable Response for GUIs
''' </summary>
''' <remarks></remarks>
Public Class ReadableResponse

    Private _Cached As Boolean
    ''' <summary>
    ''' Is Response Cached
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

    Private _Path As String
    ''' <summary>
    ''' Request Path
    ''' </summary>
    ''' <value></value>
    ''' <returns></returns>
    ''' <remarks></remarks>
    Public Property Path() As String
        Get
            Return _Path
        End Get
        Set(ByVal value As String)
            _Path = value
        End Set
    End Property


    Private _Status As Integer
    ''' <summary>
    ''' Response HTTP Status
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

    ''' <summary>
    ''' New Readable Response
    ''' </summary>
    ''' <param name="path"></param>
    ''' <param name="status"></param>
    ''' <param name="cached"></param>
    ''' <remarks></remarks>
    Public Sub New(ByVal path As String, ByVal status As Integer, ByVal cached As Boolean)

        Me.Path = path
        Me.Status = status
        Me.Cached = cached

    End Sub

End Class
