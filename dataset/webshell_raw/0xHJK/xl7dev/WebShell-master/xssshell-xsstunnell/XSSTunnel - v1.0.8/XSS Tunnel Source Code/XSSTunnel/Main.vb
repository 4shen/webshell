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
Imports XSSTunnelLib

Public Class Main

    Private Sub Main_Load(ByVal sender As System.Object, ByVal e As System.EventArgs) Handles MyBase.Load
        Me.AlwaysOnTopToolStripMenuItem.Checked = My.Settings.AlwaysOnTop
        Me.TopMost = AlwaysOnTopToolStripMenuItem.Checked
        Me.MnuEnableCache.Checked = My.Settings.CacheEnabled

        Me.TrackTrans.Value = My.Settings.Trans
        ChangeTransParency()

        FillUpInterfaces()
    End Sub


    ''' <summary>
    ''' Fill up interfaces
    ''' </summary>
    ''' <remarks></remarks>
    Private Sub FillUpInterfaces()
        'Fill up interfaces
        Dim AnyInt As Integer = CmbInterface.Items.Add("Any Interface")
        For Each IP As IPAddress In Dns.GetHostAddresses(Dns.GetHostName())
            CmbInterface.Items.Add(IP)
        Next

        CmbInterface.SelectedIndex = AnyInt
    End Sub

    Private Sub BtnStart_Click(ByVal sender As System.Object, ByVal e As System.EventArgs) Handles BtnStart.Click
        StartXSSTunnel()
    End Sub


    Private ListLock As New Object
    Private TextLock As New Object

    Private Delegate Sub DelHandleResponse(ByVal response As ReadableResponse)

    ''' <summary>
    ''' Handle responses
    ''' </summary>
    ''' <param name="response"></param>
    ''' <remarks></remarks>
    Private Sub HandleResponse(ByVal response As ReadableResponse)

        If InvokeRequired Then
            Invoke(New DelHandleResponse(AddressOf HandleResponse), response)

        Else

            UpdateStats()
            If response.Cached Then Exit Sub

            SyncLock ListLock
                Dim ResponseItem As New ListViewItem(response.Status.ToString)
                ResponseItem.SubItems.Add(response.Path)
                'ResponseItem.SubItems(1).Text = response.Path
                LstResponses.Items.Add(ResponseItem)
            End SyncLock
        End If

    End Sub

    ''' <summary>
    ''' Update Stats
    ''' </summary>
    ''' <remarks></remarks>
    Private Sub UpdateStats()
        If Proxy Is Nothing Then
            LblCached.Text = "0"
            LblRequests.Text = "0"
            LblResponses.Text = "0"

            Exit Sub
        End If


        LblCached.Text = Proxy.CachedRequests.ToString
        LblRequests.Text = Proxy.Requests.ToString
        LblResponses.Text = Proxy.Responses.ToString

        If PrgMain.Value >= PrgMain.Maximum Then
            PrgMain.Value = PrgMain.Minimum
        End If

        PrgMain.PerformStep()
    End Sub




    Private Delegate Sub DelHandleDebug(ByVal message As String, ByVal sender As Object)

    ''' <summary>
    ''' Handle Debug Messages
    ''' </summary>
    ''' <param name="message"></param>
    ''' <param name="sender"></param>
    ''' <remarks></remarks>
    Private Sub HandleDebug(ByVal message As String, ByVal sender As Object)
        If InvokeRequired Then

            Invoke(New DelHandleDebug(AddressOf HandleDebug), message, Nothing)

        Else
            SyncLock TextLock
                Try
                    TxtLog.AppendText(message & vbNewLine)
                Catch ex As Exception
                    Debug.WriteLine("Doh, max length possible. Very rare!")

                End Try

            End SyncLock

        End If
    End Sub


    Private XSSSHell As XSSShell
    Private Proxy As Proxy


    ''' <summary>
    ''' Start XSS Tunnel
    ''' </summary>
    ''' <remarks></remarks>
    Private Sub StartXSSTunnel()

        BtnStart.Enabled = False
        BtnStop.Enabled = True
        Stopped = False

        XSSSHell = New XSSShell(TxtServer.Text, TxtPassword.Text)


        'REFACTOR : Move these into ProxyBind structure
        Dim Port As Integer
        Port = CInt(NumPort.Value)
        If Port < 1 OrElse Port > 65535 Then
            Port = 0
            HandleDebug("Supplied port is wrong, Fixed to default.", Nothing)
        End If

        Dim IP As IPAddress
        If Not IPAddress.TryParse(CmbInterface.Text, IP) Then
            HandleDebug("Supplied IP is wrong, Fixed to listen all interfaces.", Nothing)
            IP = IPAddress.Any
        End If

        Dim OptionCarrier As New ProxyBind(Port, IP)

        Dim Thr As New Threading.Thread(AddressOf WaitForVictim)
        Thr.IsBackground = True
        Thr.Start(OptionCarrier)
        LblStatus.Text = "Proxy started."
    End Sub

    ''' <summary>
    ''' Proxy Bind Details
    ''' </summary>
    ''' <remarks></remarks>
    Private Structure ProxyBind
        Public Port As Integer
        Public IP As IPAddress

        Public Sub New(ByVal port As Integer, ByVal IP As IPAddress)
            Me.Port = port
            Me.IP = IP
        End Sub
    End Structure

    ''' <summary>
    ''' Wait for victim
    ''' </summary>
    ''' <remarks></remarks>
    Private Sub WaitForVictim(ByVal proxyBind As Object)

        Dim BindDetails As ProxyBind = DirectCast(proxyBind, ProxyBind)

        If XSSSHell Is Nothing Then
            MessageBox.Show("XSS Shell is Nothing !")
            Exit Sub
        End If

        'Check communication
        Try

            If Not XSSSHell.CheckServer() Then

                MessageBox.Show("XSS Shell Server is not working!" & vbNewLine & "Modify settings and try again.", "XSS Shell Server Error", MessageBoxButtons.OK, MessageBoxIcon.Error)

                StopServer()
                Exit Sub

            End If

        Catch ex As Exception

            MessageBox.Show("XSS Shell Server Internal Error. Details :" & vbNewLine & ex.ToString, "XSS Shell Server Error", MessageBoxButtons.OK, MessageBoxIcon.Error)
            StopServer()
            Exit Sub


        End Try

        While Not XSSSHell.IsVictimAlive
            If Stopped Then Exit Sub
            ManageProgress(3)
            Threading.Thread.Sleep(500)
        End While

        ManageProgress(2)
        ManageProgress(XSSSHell.VictimId)
        My.Computer.Audio.PlaySystemSound(Media.SystemSounds.Asterisk)

        StartProxy(BindDetails.Port, BindDetails.IP)

    End Sub


    ''' <summary>
    ''' Proxy
    ''' </summary>
    ''' <remarks></remarks>
    Private Sub StartProxy(ByVal port As Integer, ByVal IP As IPAddress)
        If XSSSHell Is Nothing Then
            MessageBox.Show("XSS Shell is not active", "Proxy Startup Error", MessageBoxButtons.OK, MessageBoxIcon.Exclamation)
            Exit Sub
        End If

        Proxy = New Proxy(XSSSHell, port, IP)
        Proxy.CacheEnabled = MnuEnableCache.Checked

        AddHandler Proxy.DebugMessageSent, AddressOf HandleDebug
        AddHandler Proxy.ResponseReceived, AddressOf HandleResponse


        Dim Thr As New Threading.Thread(AddressOf Proxy.Listen)
        Thr.Start()
    End Sub



    Private Stopped As Boolean


    Private Delegate Sub DelStopServer()

    ''' <summary>
    ''' Stop Server
    ''' </summary>
    ''' <remarks></remarks>
    Private Sub StopServer()
        If InvokeRequired Then
            Invoke(New DelStopServer(AddressOf StopServer))

        Else
            Stopped = True
            BtnStart.Enabled = True
            BtnStop.Enabled = False
            PrgVictim.Value = PrgVictim.Minimum
            If Proxy IsNot Nothing Then
                Proxy.Stop()
                RemoveHandler Proxy.DebugMessageSent, AddressOf HandleDebug
                RemoveHandler Proxy.ResponseReceived, AddressOf HandleResponse
            End If

            LblStatus.Text = "Proxy stopped."
        End If
    End Sub

    Private Delegate Sub DelManageProgress(ByVal status As Integer)
    Private Sub ManageProgress(ByVal status As Integer)
        If InvokeRequired Then
            Invoke(New DelManageProgress(AddressOf ManageProgress), status)

        Else
            Select Case status
                Case 0
                    PrgVictim.Enabled = True

                Case 1
                    PrgVictim.Enabled = False

                Case 2
                    PrgVictim.Value = PrgVictim.Maximum

                Case 3
                    If PrgVictim.Value >= PrgVictim.Maximum Then
                        PrgVictim.Value = PrgVictim.Minimum
                    End If

                    PrgVictim.PerformStep()

                Case Else
                    LblVictimID.Text = status.ToString

            End Select


        End If
    End Sub

    Private Sub BtnStop_Click(ByVal sender As System.Object, ByVal e As System.EventArgs) Handles BtnStop.Click
        StopServer()
    End Sub

    Private Sub ExitToolStripMenuItem_Click(ByVal sender As System.Object, ByVal e As System.EventArgs) Handles ExitToolStripMenuItem.Click
        Quit()
        Application.Exit()
    End Sub

    ''' <summary>
    ''' Release stuff
    ''' </summary>
    ''' <remarks></remarks>
    Private Sub Quit()
        StopServer()
        My.Settings.Save()
    End Sub

    ''' <summary>
    ''' Stop Proxy and Quit
    ''' </summary>
    ''' <remarks></remarks>
    Protected Overrides Sub Finalize()
        Quit()
        MyBase.Finalize()
    End Sub

    ''' <summary>
    ''' Check XSS Shell Server
    ''' </summary>
    ''' <param name="sender"></param>
    ''' <param name="e"></param>
    ''' <remarks></remarks>
    Private Sub BtnTestServer_Click(ByVal sender As System.Object, ByVal e As System.EventArgs) Handles BtnTestServer.Click
        Dim CheckShell As New XSSShell(TxtServer.Text, TxtPassword.Text)
        If CheckShell.CheckServer() Then
            MessageBox.Show("Successfully connected to XSS Shell", "XSS Shell Connection is Working", MessageBoxButtons.OK, MessageBoxIcon.Information)

        Else
            MessageBox.Show("Connection couldn't established. Please try again", "XSS Shell connection is not working", MessageBoxButtons.OK, MessageBoxIcon.Error)

        End If
    End Sub

    Private Shadows Sub FormClosing(ByVal sender As System.Object, ByVal e As System.ComponentModel.CancelEventArgs) Handles MyBase.Closing
        Quit()
    End Sub



    Private Sub ClearLogToolStripMenuItem_Click(ByVal sender As System.Object, ByVal e As System.EventArgs) Handles ClearLogToolStripMenuItem.Click
        TxtLog.Clear()
    End Sub

    Private Sub AlwaysOnTopToolStripMenuItem_Click(ByVal sender As System.Object, ByVal e As System.EventArgs) Handles AlwaysOnTopToolStripMenuItem.Click
        My.Settings.AlwaysOnTop = AlwaysOnTopToolStripMenuItem.Checked
        Me.TopMost = AlwaysOnTopToolStripMenuItem.Checked
    End Sub

    Private Sub TrackTrans_Scroll(ByVal sender As System.Object, ByVal e As System.EventArgs) Handles TrackTrans.Scroll
        ChangeTransParency()

    End Sub

    Private Sub ChangeTransParency()

        Me.Opacity = (TrackTrans.Value / 100)
        My.Settings.Trans = TrackTrans.Value

    End Sub

    Private Sub AboutToolStripMenuItem_Click(ByVal sender As System.Object, ByVal e As System.EventArgs) Handles AboutToolStripMenuItem.Click
        Dim Frm As New AboutBox()
        Frm.Show()
    End Sub

    Private Sub PortcullisComputerSecurityWebsiteToolStripMenuItem_Click(ByVal sender As System.Object, ByVal e As System.EventArgs) Handles PortcullisComputerSecurityWebsiteToolStripMenuItem.Click, GoToXSSShellDownloadPageToolStripMenuItem.Click


        Dim URL As String = "http://www.portcullis-security.com/16.php?XSSSHELL"
        Try
            Diagnostics.Process.Start(URL)

        Catch ex As Exception
            MessageBox.Show("Somehow I failed to launch your bloody browser. Anyway, here is the URL help yourself : " & URL, "Douh!", MessageBoxButtons.OK, MessageBoxIcon.Error)

        End Try

    End Sub

    ''' <summary>
    ''' Clear Cache
    ''' </summary>
    ''' <param name="sender"></param>
    ''' <param name="e"></param>
    ''' <remarks></remarks>
    Private Sub ClearCacheToolStripMenuItem_Click(ByVal sender As System.Object, ByVal e As System.EventArgs) Handles ClearCacheToolStripMenuItem.Click
        LblStatus.Text = "Clearing."
        Application.DoEvents()

        XSSTunnelLib.Proxy.ClearCache()

        LblStatus.Text = "Cache cleared."
        Application.DoEvents()

    End Sub

    Private Sub ClearItemsToolStripMenuItem_Click(ByVal sender As System.Object, ByVal e As System.EventArgs) Handles ClearItemsToolStripMenuItem.Click
        LstResponses.Items.Clear()
    End Sub

    Private Sub MnuEnableCache_Click(ByVal sender As System.Object, ByVal e As System.EventArgs) Handles MnuEnableCache.Click
        My.Settings.CacheEnabled = MnuEnableCache.Checked
        LblStatus.Text = "Restart proxy to enable new settings."
    End Sub
End Class
