<Global.Microsoft.VisualBasic.CompilerServices.DesignerGenerated()> _
Partial Class Main
    Inherits System.Windows.Forms.Form

    'Form overrides dispose to clean up the component list.
    <System.Diagnostics.DebuggerNonUserCode()> _
    Protected Overrides Sub Dispose(ByVal disposing As Boolean)
        If disposing AndAlso components IsNot Nothing Then
            components.Dispose()
        End If
        MyBase.Dispose(disposing)
    End Sub

    'Required by the Windows Form Designer
    Private components As System.ComponentModel.IContainer

    'NOTE: The following procedure is required by the Windows Form Designer
    'It can be modified using the Windows Form Designer.  
    'Do not modify it using the code editor.
    <System.Diagnostics.DebuggerStepThrough()> _
    Private Sub InitializeComponent()
        Me.components = New System.ComponentModel.Container
        Dim resources As System.ComponentModel.ComponentResourceManager = New System.ComponentModel.ComponentResourceManager(GetType(Main))
        Me.ToolStrip1 = New System.Windows.Forms.ToolStrip
        Me.BtnStart = New System.Windows.Forms.ToolStripButton
        Me.ToolStripSeparator1 = New System.Windows.Forms.ToolStripSeparator
        Me.BtnStop = New System.Windows.Forms.ToolStripButton
        Me.TabControl1 = New System.Windows.Forms.TabControl
        Me.TabPage2 = New System.Windows.Forms.TabPage
        Me.LstResponses = New System.Windows.Forms.ListView
        Me.ColumnHeader1 = New System.Windows.Forms.ColumnHeader
        Me.ColumnHeader2 = New System.Windows.Forms.ColumnHeader
        Me.MnuList = New System.Windows.Forms.ContextMenuStrip(Me.components)
        Me.ClearItemsToolStripMenuItem = New System.Windows.Forms.ToolStripMenuItem
        Me.GroupBox4 = New System.Windows.Forms.GroupBox
        Me.LblCached = New System.Windows.Forms.Label
        Me.LblResponses = New System.Windows.Forms.Label
        Me.LblRequests = New System.Windows.Forms.Label
        Me.Label9 = New System.Windows.Forms.Label
        Me.Label7 = New System.Windows.Forms.Label
        Me.Label8 = New System.Windows.Forms.Label
        Me.GroupBox3 = New System.Windows.Forms.GroupBox
        Me.PrgVictim = New System.Windows.Forms.ProgressBar
        Me.LblVictimID = New System.Windows.Forms.Label
        Me.Label5 = New System.Windows.Forms.Label
        Me.TabPage1 = New System.Windows.Forms.TabPage
        Me.GroupBox5 = New System.Windows.Forms.GroupBox
        Me.TrackTrans = New System.Windows.Forms.TrackBar
        Me.GroupBox1 = New System.Windows.Forms.GroupBox
        Me.Label4 = New System.Windows.Forms.Label
        Me.CmbInterface = New System.Windows.Forms.ComboBox
        Me.NumPort = New System.Windows.Forms.NumericUpDown
        Me.Label1 = New System.Windows.Forms.Label
        Me.GroupBox2 = New System.Windows.Forms.GroupBox
        Me.BtnTestServer = New System.Windows.Forms.Button
        Me.TxtPassword = New System.Windows.Forms.TextBox
        Me.Label3 = New System.Windows.Forms.Label
        Me.TxtServer = New System.Windows.Forms.TextBox
        Me.Label2 = New System.Windows.Forms.Label
        Me.TabPage3 = New System.Windows.Forms.TabPage
        Me.TxtLog = New System.Windows.Forms.TextBox
        Me.MnuLog = New System.Windows.Forms.ContextMenuStrip(Me.components)
        Me.ClearLogToolStripMenuItem = New System.Windows.Forms.ToolStripMenuItem
        Me.MenuStrip1 = New System.Windows.Forms.MenuStrip
        Me.FileToolStripMenuItem = New System.Windows.Forms.ToolStripMenuItem
        Me.ToolStripButton2 = New System.Windows.Forms.ToolStripMenuItem
        Me.toolStripSeparator2 = New System.Windows.Forms.ToolStripSeparator
        Me.ExitToolStripMenuItem = New System.Windows.Forms.ToolStripMenuItem
        Me.CacheToolStripMenuItem = New System.Windows.Forms.ToolStripMenuItem
        Me.ClearCacheToolStripMenuItem = New System.Windows.Forms.ToolStripMenuItem
        Me.ViewToolStripMenuItem = New System.Windows.Forms.ToolStripMenuItem
        Me.AlwaysOnTopToolStripMenuItem = New System.Windows.Forms.ToolStripMenuItem
        Me.HelpToolStripMenuItem = New System.Windows.Forms.ToolStripMenuItem
        Me.AboutToolStripMenuItem = New System.Windows.Forms.ToolStripMenuItem
        Me.ToolStripSeparator3 = New System.Windows.Forms.ToolStripSeparator
        Me.PortcullisComputerSecurityWebsiteToolStripMenuItem = New System.Windows.Forms.ToolStripMenuItem
        Me.GoToXSSShellDownloadPageToolStripMenuItem = New System.Windows.Forms.ToolStripMenuItem
        Me.StatusStrip1 = New System.Windows.Forms.StatusStrip
        Me.PrgMain = New System.Windows.Forms.ToolStripProgressBar
        Me.LblStatus = New System.Windows.Forms.ToolStripStatusLabel
        Me.MnuEnableCache = New System.Windows.Forms.ToolStripMenuItem
        Me.ToolStrip1.SuspendLayout()
        Me.TabControl1.SuspendLayout()
        Me.TabPage2.SuspendLayout()
        Me.MnuList.SuspendLayout()
        Me.GroupBox4.SuspendLayout()
        Me.GroupBox3.SuspendLayout()
        Me.TabPage1.SuspendLayout()
        Me.GroupBox5.SuspendLayout()
        CType(Me.TrackTrans, System.ComponentModel.ISupportInitialize).BeginInit()
        Me.GroupBox1.SuspendLayout()
        CType(Me.NumPort, System.ComponentModel.ISupportInitialize).BeginInit()
        Me.GroupBox2.SuspendLayout()
        Me.TabPage3.SuspendLayout()
        Me.MnuLog.SuspendLayout()
        Me.MenuStrip1.SuspendLayout()
        Me.StatusStrip1.SuspendLayout()
        Me.SuspendLayout()
        '
        'ToolStrip1
        '
        Me.ToolStrip1.Items.AddRange(New System.Windows.Forms.ToolStripItem() {Me.BtnStart, Me.ToolStripSeparator1, Me.BtnStop})
        Me.ToolStrip1.Location = New System.Drawing.Point(0, 24)
        Me.ToolStrip1.Name = "ToolStrip1"
        Me.ToolStrip1.Size = New System.Drawing.Size(411, 25)
        Me.ToolStrip1.TabIndex = 0
        Me.ToolStrip1.Text = "ToolStrip1"
        '
        'BtnStart
        '
        Me.BtnStart.Image = CType(resources.GetObject("BtnStart.Image"), System.Drawing.Image)
        Me.BtnStart.ImageTransparentColor = System.Drawing.Color.Magenta
        Me.BtnStart.Name = "BtnStart"
        Me.BtnStart.Size = New System.Drawing.Size(107, 22)
        Me.BtnStart.Text = "Start XSS Tunnel"
        '
        'ToolStripSeparator1
        '
        Me.ToolStripSeparator1.Name = "ToolStripSeparator1"
        Me.ToolStripSeparator1.Size = New System.Drawing.Size(6, 25)
        '
        'BtnStop
        '
        Me.BtnStop.Enabled = False
        Me.BtnStop.Image = CType(resources.GetObject("BtnStop.Image"), System.Drawing.Image)
        Me.BtnStop.ImageTransparentColor = System.Drawing.Color.Magenta
        Me.BtnStop.Name = "BtnStop"
        Me.BtnStop.Size = New System.Drawing.Size(105, 22)
        Me.BtnStop.Text = "Sto&p XSS Tunnel"
        '
        'TabControl1
        '
        Me.TabControl1.Anchor = CType((((System.Windows.Forms.AnchorStyles.Top Or System.Windows.Forms.AnchorStyles.Bottom) _
                    Or System.Windows.Forms.AnchorStyles.Left) _
                    Or System.Windows.Forms.AnchorStyles.Right), System.Windows.Forms.AnchorStyles)
        Me.TabControl1.Controls.Add(Me.TabPage2)
        Me.TabControl1.Controls.Add(Me.TabPage1)
        Me.TabControl1.Controls.Add(Me.TabPage3)
        Me.TabControl1.Location = New System.Drawing.Point(0, 49)
        Me.TabControl1.Multiline = True
        Me.TabControl1.Name = "TabControl1"
        Me.TabControl1.SelectedIndex = 0
        Me.TabControl1.Size = New System.Drawing.Size(411, 246)
        Me.TabControl1.TabIndex = 2
        '
        'TabPage2
        '
        Me.TabPage2.Controls.Add(Me.LstResponses)
        Me.TabPage2.Controls.Add(Me.GroupBox4)
        Me.TabPage2.Controls.Add(Me.GroupBox3)
        Me.TabPage2.Location = New System.Drawing.Point(4, 22)
        Me.TabPage2.Name = "TabPage2"
        Me.TabPage2.Padding = New System.Windows.Forms.Padding(3)
        Me.TabPage2.Size = New System.Drawing.Size(403, 220)
        Me.TabPage2.TabIndex = 1
        Me.TabPage2.Text = "Dashboard"
        Me.TabPage2.UseVisualStyleBackColor = True
        '
        'LstResponses
        '
        Me.LstResponses.Anchor = CType((((System.Windows.Forms.AnchorStyles.Top Or System.Windows.Forms.AnchorStyles.Bottom) _
                    Or System.Windows.Forms.AnchorStyles.Left) _
                    Or System.Windows.Forms.AnchorStyles.Right), System.Windows.Forms.AnchorStyles)
        Me.LstResponses.Columns.AddRange(New System.Windows.Forms.ColumnHeader() {Me.ColumnHeader1, Me.ColumnHeader2})
        Me.LstResponses.ContextMenuStrip = Me.MnuList
        Me.LstResponses.FullRowSelect = True
        Me.LstResponses.GridLines = True
        Me.LstResponses.Location = New System.Drawing.Point(3, 77)
        Me.LstResponses.Name = "LstResponses"
        Me.LstResponses.Size = New System.Drawing.Size(397, 140)
        Me.LstResponses.TabIndex = 3
        Me.LstResponses.UseCompatibleStateImageBehavior = False
        Me.LstResponses.View = System.Windows.Forms.View.Details
        '
        'ColumnHeader1
        '
        Me.ColumnHeader1.Text = "Status"
        '
        'ColumnHeader2
        '
        Me.ColumnHeader2.Text = "Requested Path"
        Me.ColumnHeader2.Width = 307
        '
        'MnuList
        '
        Me.MnuList.Items.AddRange(New System.Windows.Forms.ToolStripItem() {Me.ClearItemsToolStripMenuItem})
        Me.MnuList.Name = "MnuList"
        Me.MnuList.Size = New System.Drawing.Size(141, 26)
        '
        'ClearItemsToolStripMenuItem
        '
        Me.ClearItemsToolStripMenuItem.Name = "ClearItemsToolStripMenuItem"
        Me.ClearItemsToolStripMenuItem.Size = New System.Drawing.Size(140, 22)
        Me.ClearItemsToolStripMenuItem.Text = "&Clear Items"
        '
        'GroupBox4
        '
        Me.GroupBox4.Controls.Add(Me.LblCached)
        Me.GroupBox4.Controls.Add(Me.LblResponses)
        Me.GroupBox4.Controls.Add(Me.LblRequests)
        Me.GroupBox4.Controls.Add(Me.Label9)
        Me.GroupBox4.Controls.Add(Me.Label7)
        Me.GroupBox4.Controls.Add(Me.Label8)
        Me.GroupBox4.Location = New System.Drawing.Point(176, 6)
        Me.GroupBox4.Name = "GroupBox4"
        Me.GroupBox4.Size = New System.Drawing.Size(205, 71)
        Me.GroupBox4.TabIndex = 2
        Me.GroupBox4.TabStop = False
        Me.GroupBox4.Text = "Stats"
        '
        'LblCached
        '
        Me.LblCached.AutoSize = True
        Me.LblCached.Location = New System.Drawing.Point(129, 51)
        Me.LblCached.Name = "LblCached"
        Me.LblCached.Size = New System.Drawing.Size(13, 13)
        Me.LblCached.TabIndex = 5
        Me.LblCached.Text = "0"
        '
        'LblResponses
        '
        Me.LblResponses.AutoSize = True
        Me.LblResponses.Location = New System.Drawing.Point(129, 34)
        Me.LblResponses.Name = "LblResponses"
        Me.LblResponses.Size = New System.Drawing.Size(13, 13)
        Me.LblResponses.TabIndex = 4
        Me.LblResponses.Text = "0"
        '
        'LblRequests
        '
        Me.LblRequests.AutoSize = True
        Me.LblRequests.Location = New System.Drawing.Point(129, 16)
        Me.LblRequests.Name = "LblRequests"
        Me.LblRequests.Size = New System.Drawing.Size(13, 13)
        Me.LblRequests.TabIndex = 3
        Me.LblRequests.Text = "0"
        '
        'Label9
        '
        Me.Label9.AutoSize = True
        Me.Label9.Location = New System.Drawing.Point(23, 51)
        Me.Label9.Name = "Label9"
        Me.Label9.Size = New System.Drawing.Size(109, 13)
        Me.Label9.TabIndex = 2
        Me.Label9.Text = "Cached Responses : "
        '
        'Label7
        '
        Me.Label7.AutoSize = True
        Me.Label7.Location = New System.Drawing.Point(6, 34)
        Me.Label7.Name = "Label7"
        Me.Label7.Size = New System.Drawing.Size(126, 13)
        Me.Label7.TabIndex = 1
        Me.Label7.Text = "Responses From Victim : "
        '
        'Label8
        '
        Me.Label8.AutoSize = True
        Me.Label8.Location = New System.Drawing.Point(16, 16)
        Me.Label8.Name = "Label8"
        Me.Label8.Size = New System.Drawing.Size(116, 13)
        Me.Label8.TabIndex = 0
        Me.Label8.Text = "Requests From Client : "
        '
        'GroupBox3
        '
        Me.GroupBox3.Controls.Add(Me.PrgVictim)
        Me.GroupBox3.Controls.Add(Me.LblVictimID)
        Me.GroupBox3.Controls.Add(Me.Label5)
        Me.GroupBox3.Location = New System.Drawing.Point(3, 6)
        Me.GroupBox3.Name = "GroupBox3"
        Me.GroupBox3.Size = New System.Drawing.Size(167, 71)
        Me.GroupBox3.TabIndex = 0
        Me.GroupBox3.TabStop = False
        Me.GroupBox3.Text = "Connected Victim"
        '
        'PrgVictim
        '
        Me.PrgVictim.Location = New System.Drawing.Point(3, 55)
        Me.PrgVictim.Maximum = 20
        Me.PrgVictim.Name = "PrgVictim"
        Me.PrgVictim.Size = New System.Drawing.Size(158, 10)
        Me.PrgVictim.Step = 1
        Me.PrgVictim.Style = System.Windows.Forms.ProgressBarStyle.Continuous
        Me.PrgVictim.TabIndex = 2
        '
        'LblVictimID
        '
        Me.LblVictimID.AutoSize = True
        Me.LblVictimID.Font = New System.Drawing.Font("Microsoft Sans Serif", 8.25!, System.Drawing.FontStyle.Bold, System.Drawing.GraphicsUnit.Point, CType(162, Byte))
        Me.LblVictimID.Location = New System.Drawing.Point(70, 16)
        Me.LblVictimID.Name = "LblVictimID"
        Me.LblVictimID.Size = New System.Drawing.Size(14, 13)
        Me.LblVictimID.TabIndex = 1
        Me.LblVictimID.Text = "?"
        '
        'Label5
        '
        Me.Label5.AutoSize = True
        Me.Label5.Location = New System.Drawing.Point(6, 16)
        Me.Label5.Name = "Label5"
        Me.Label5.Size = New System.Drawing.Size(58, 13)
        Me.Label5.TabIndex = 0
        Me.Label5.Text = "Victim ID : "
        '
        'TabPage1
        '
        Me.TabPage1.Controls.Add(Me.GroupBox5)
        Me.TabPage1.Controls.Add(Me.GroupBox1)
        Me.TabPage1.Controls.Add(Me.GroupBox2)
        Me.TabPage1.Location = New System.Drawing.Point(4, 22)
        Me.TabPage1.Name = "TabPage1"
        Me.TabPage1.Padding = New System.Windows.Forms.Padding(3)
        Me.TabPage1.Size = New System.Drawing.Size(403, 220)
        Me.TabPage1.TabIndex = 0
        Me.TabPage1.Text = "Options"
        Me.TabPage1.UseVisualStyleBackColor = True
        '
        'GroupBox5
        '
        Me.GroupBox5.Controls.Add(Me.TrackTrans)
        Me.GroupBox5.Dock = System.Windows.Forms.DockStyle.Top
        Me.GroupBox5.Location = New System.Drawing.Point(3, 132)
        Me.GroupBox5.Name = "GroupBox5"
        Me.GroupBox5.Size = New System.Drawing.Size(397, 74)
        Me.GroupBox5.TabIndex = 4
        Me.GroupBox5.TabStop = False
        Me.GroupBox5.Text = "Transparency"
        '
        'TrackTrans
        '
        Me.TrackTrans.LargeChange = 10
        Me.TrackTrans.Location = New System.Drawing.Point(5, 19)
        Me.TrackTrans.Maximum = 100
        Me.TrackTrans.Minimum = 10
        Me.TrackTrans.Name = "TrackTrans"
        Me.TrackTrans.Size = New System.Drawing.Size(370, 45)
        Me.TrackTrans.TabIndex = 0
        Me.TrackTrans.TickStyle = System.Windows.Forms.TickStyle.None
        Me.TrackTrans.Value = 10
        '
        'GroupBox1
        '
        Me.GroupBox1.Controls.Add(Me.Label4)
        Me.GroupBox1.Controls.Add(Me.CmbInterface)
        Me.GroupBox1.Controls.Add(Me.NumPort)
        Me.GroupBox1.Controls.Add(Me.Label1)
        Me.GroupBox1.Dock = System.Windows.Forms.DockStyle.Top
        Me.GroupBox1.Location = New System.Drawing.Point(3, 82)
        Me.GroupBox1.Name = "GroupBox1"
        Me.GroupBox1.Size = New System.Drawing.Size(397, 50)
        Me.GroupBox1.TabIndex = 2
        Me.GroupBox1.TabStop = False
        Me.GroupBox1.Text = "Proxy Listener"
        '
        'Label4
        '
        Me.Label4.AutoSize = True
        Me.Label4.Location = New System.Drawing.Point(298, 21)
        Me.Label4.Name = "Label4"
        Me.Label4.Size = New System.Drawing.Size(13, 13)
        Me.Label4.TabIndex = 4
        Me.Label4.Text = " :"
        '
        'CmbInterface
        '
        Me.CmbInterface.FormattingEnabled = True
        Me.CmbInterface.Location = New System.Drawing.Point(53, 19)
        Me.CmbInterface.Name = "CmbInterface"
        Me.CmbInterface.Size = New System.Drawing.Size(239, 21)
        Me.CmbInterface.TabIndex = 3
        '
        'NumPort
        '
        Me.NumPort.Location = New System.Drawing.Point(315, 19)
        Me.NumPort.Maximum = New Decimal(New Integer() {65535, 0, 0, 0})
        Me.NumPort.Minimum = New Decimal(New Integer() {1, 0, 0, 0})
        Me.NumPort.Name = "NumPort"
        Me.NumPort.Size = New System.Drawing.Size(57, 20)
        Me.NumPort.TabIndex = 2
        Me.NumPort.Value = New Decimal(New Integer() {8080, 0, 0, 0})
        '
        'Label1
        '
        Me.Label1.AutoSize = True
        Me.Label1.Location = New System.Drawing.Point(6, 21)
        Me.Label1.Name = "Label1"
        Me.Label1.Size = New System.Drawing.Size(41, 13)
        Me.Label1.TabIndex = 1
        Me.Label1.Text = "Listen :"
        '
        'GroupBox2
        '
        Me.GroupBox2.Controls.Add(Me.BtnTestServer)
        Me.GroupBox2.Controls.Add(Me.TxtPassword)
        Me.GroupBox2.Controls.Add(Me.Label3)
        Me.GroupBox2.Controls.Add(Me.TxtServer)
        Me.GroupBox2.Controls.Add(Me.Label2)
        Me.GroupBox2.Dock = System.Windows.Forms.DockStyle.Top
        Me.GroupBox2.Location = New System.Drawing.Point(3, 3)
        Me.GroupBox2.Name = "GroupBox2"
        Me.GroupBox2.Size = New System.Drawing.Size(397, 79)
        Me.GroupBox2.TabIndex = 3
        Me.GroupBox2.TabStop = False
        Me.GroupBox2.Text = "XSS Shell Configuration"
        '
        'BtnTestServer
        '
        Me.BtnTestServer.Location = New System.Drawing.Point(189, 44)
        Me.BtnTestServer.Name = "BtnTestServer"
        Me.BtnTestServer.Size = New System.Drawing.Size(75, 23)
        Me.BtnTestServer.TabIndex = 4
        Me.BtnTestServer.Text = "&Test Server"
        Me.BtnTestServer.UseVisualStyleBackColor = True
        '
        'TxtPassword
        '
        Me.TxtPassword.Location = New System.Drawing.Point(71, 46)
        Me.TxtPassword.Name = "TxtPassword"
        Me.TxtPassword.Size = New System.Drawing.Size(112, 20)
        Me.TxtPassword.TabIndex = 3
        Me.TxtPassword.Text = "w00t"
        Me.TxtPassword.UseSystemPasswordChar = True
        '
        'Label3
        '
        Me.Label3.AutoSize = True
        Me.Label3.Location = New System.Drawing.Point(6, 49)
        Me.Label3.Name = "Label3"
        Me.Label3.Size = New System.Drawing.Size(59, 13)
        Me.Label3.TabIndex = 2
        Me.Label3.Text = "Password :"
        '
        'TxtServer
        '
        Me.TxtServer.Location = New System.Drawing.Point(71, 20)
        Me.TxtServer.Name = "TxtServer"
        Me.TxtServer.Size = New System.Drawing.Size(301, 20)
        Me.TxtServer.TabIndex = 1
        Me.TxtServer.Text = "http://www.xssshellserver.com:60000/admin/"
        '
        'Label2
        '
        Me.Label2.AutoSize = True
        Me.Label2.Location = New System.Drawing.Point(21, 23)
        Me.Label2.Name = "Label2"
        Me.Label2.Size = New System.Drawing.Size(44, 13)
        Me.Label2.TabIndex = 0
        Me.Label2.Text = "Server :"
        '
        'TabPage3
        '
        Me.TabPage3.Controls.Add(Me.TxtLog)
        Me.TabPage3.Location = New System.Drawing.Point(4, 22)
        Me.TabPage3.Name = "TabPage3"
        Me.TabPage3.Padding = New System.Windows.Forms.Padding(3)
        Me.TabPage3.Size = New System.Drawing.Size(403, 220)
        Me.TabPage3.TabIndex = 2
        Me.TabPage3.Text = "Log"
        Me.TabPage3.UseVisualStyleBackColor = True
        '
        'TxtLog
        '
        Me.TxtLog.ContextMenuStrip = Me.MnuLog
        Me.TxtLog.Dock = System.Windows.Forms.DockStyle.Fill
        Me.TxtLog.Location = New System.Drawing.Point(3, 3)
        Me.TxtLog.MaxLength = 3276700
        Me.TxtLog.Multiline = True
        Me.TxtLog.Name = "TxtLog"
        Me.TxtLog.ScrollBars = System.Windows.Forms.ScrollBars.Vertical
        Me.TxtLog.Size = New System.Drawing.Size(397, 214)
        Me.TxtLog.TabIndex = 0
        '
        'MnuLog
        '
        Me.MnuLog.Items.AddRange(New System.Windows.Forms.ToolStripItem() {Me.ClearLogToolStripMenuItem})
        Me.MnuLog.Name = "MnuLog"
        Me.MnuLog.Size = New System.Drawing.Size(131, 26)
        '
        'ClearLogToolStripMenuItem
        '
        Me.ClearLogToolStripMenuItem.Name = "ClearLogToolStripMenuItem"
        Me.ClearLogToolStripMenuItem.Size = New System.Drawing.Size(130, 22)
        Me.ClearLogToolStripMenuItem.Text = "&Clear Log"
        '
        'MenuStrip1
        '
        Me.MenuStrip1.Items.AddRange(New System.Windows.Forms.ToolStripItem() {Me.FileToolStripMenuItem, Me.CacheToolStripMenuItem, Me.ViewToolStripMenuItem, Me.HelpToolStripMenuItem})
        Me.MenuStrip1.Location = New System.Drawing.Point(0, 0)
        Me.MenuStrip1.Name = "MenuStrip1"
        Me.MenuStrip1.Size = New System.Drawing.Size(411, 24)
        Me.MenuStrip1.TabIndex = 3
        Me.MenuStrip1.Text = "MenuStrip1"
        '
        'FileToolStripMenuItem
        '
        Me.FileToolStripMenuItem.DropDownItems.AddRange(New System.Windows.Forms.ToolStripItem() {Me.ToolStripButton2, Me.toolStripSeparator2, Me.ExitToolStripMenuItem})
        Me.FileToolStripMenuItem.Name = "FileToolStripMenuItem"
        Me.FileToolStripMenuItem.Size = New System.Drawing.Size(35, 20)
        Me.FileToolStripMenuItem.Text = "&File"
        '
        'ToolStripButton2
        '
        Me.ToolStripButton2.Image = CType(resources.GetObject("ToolStripButton2.Image"), System.Drawing.Image)
        Me.ToolStripButton2.ImageTransparentColor = System.Drawing.Color.Magenta
        Me.ToolStripButton2.Name = "ToolStripButton2"
        Me.ToolStripButton2.Size = New System.Drawing.Size(165, 22)
        Me.ToolStripButton2.Text = "&Start XSS Tunnel"
        '
        'toolStripSeparator2
        '
        Me.toolStripSeparator2.Name = "toolStripSeparator2"
        Me.toolStripSeparator2.Size = New System.Drawing.Size(162, 6)
        '
        'ExitToolStripMenuItem
        '
        Me.ExitToolStripMenuItem.Name = "ExitToolStripMenuItem"
        Me.ExitToolStripMenuItem.Size = New System.Drawing.Size(165, 22)
        Me.ExitToolStripMenuItem.Text = "E&xit"
        '
        'CacheToolStripMenuItem
        '
        Me.CacheToolStripMenuItem.DropDownItems.AddRange(New System.Windows.Forms.ToolStripItem() {Me.MnuEnableCache, Me.ClearCacheToolStripMenuItem})
        Me.CacheToolStripMenuItem.Name = "CacheToolStripMenuItem"
        Me.CacheToolStripMenuItem.Size = New System.Drawing.Size(49, 20)
        Me.CacheToolStripMenuItem.Text = "&Cache"
        '
        'ClearCacheToolStripMenuItem
        '
        Me.ClearCacheToolStripMenuItem.Name = "ClearCacheToolStripMenuItem"
        Me.ClearCacheToolStripMenuItem.Size = New System.Drawing.Size(152, 22)
        Me.ClearCacheToolStripMenuItem.Text = "&Clear Cache"
        '
        'ViewToolStripMenuItem
        '
        Me.ViewToolStripMenuItem.DropDownItems.AddRange(New System.Windows.Forms.ToolStripItem() {Me.AlwaysOnTopToolStripMenuItem})
        Me.ViewToolStripMenuItem.Name = "ViewToolStripMenuItem"
        Me.ViewToolStripMenuItem.Size = New System.Drawing.Size(41, 20)
        Me.ViewToolStripMenuItem.Text = "&View"
        '
        'AlwaysOnTopToolStripMenuItem
        '
        Me.AlwaysOnTopToolStripMenuItem.Checked = True
        Me.AlwaysOnTopToolStripMenuItem.CheckOnClick = True
        Me.AlwaysOnTopToolStripMenuItem.CheckState = System.Windows.Forms.CheckState.Checked
        Me.AlwaysOnTopToolStripMenuItem.Name = "AlwaysOnTopToolStripMenuItem"
        Me.AlwaysOnTopToolStripMenuItem.Size = New System.Drawing.Size(157, 22)
        Me.AlwaysOnTopToolStripMenuItem.Text = "&Always On Top"
        '
        'HelpToolStripMenuItem
        '
        Me.HelpToolStripMenuItem.DropDownItems.AddRange(New System.Windows.Forms.ToolStripItem() {Me.AboutToolStripMenuItem, Me.ToolStripSeparator3, Me.PortcullisComputerSecurityWebsiteToolStripMenuItem, Me.GoToXSSShellDownloadPageToolStripMenuItem})
        Me.HelpToolStripMenuItem.Name = "HelpToolStripMenuItem"
        Me.HelpToolStripMenuItem.Size = New System.Drawing.Size(40, 20)
        Me.HelpToolStripMenuItem.Text = "&Help"
        '
        'AboutToolStripMenuItem
        '
        Me.AboutToolStripMenuItem.Name = "AboutToolStripMenuItem"
        Me.AboutToolStripMenuItem.Size = New System.Drawing.Size(234, 22)
        Me.AboutToolStripMenuItem.Text = "&About..."
        '
        'ToolStripSeparator3
        '
        Me.ToolStripSeparator3.Name = "ToolStripSeparator3"
        Me.ToolStripSeparator3.Size = New System.Drawing.Size(231, 6)
        '
        'PortcullisComputerSecurityWebsiteToolStripMenuItem
        '
        Me.PortcullisComputerSecurityWebsiteToolStripMenuItem.Name = "PortcullisComputerSecurityWebsiteToolStripMenuItem"
        Me.PortcullisComputerSecurityWebsiteToolStripMenuItem.Size = New System.Drawing.Size(234, 22)
        Me.PortcullisComputerSecurityWebsiteToolStripMenuItem.Text = "&Go To Website"
        '
        'GoToXSSShellDownloadPageToolStripMenuItem
        '
        Me.GoToXSSShellDownloadPageToolStripMenuItem.Name = "GoToXSSShellDownloadPageToolStripMenuItem"
        Me.GoToXSSShellDownloadPageToolStripMenuItem.Size = New System.Drawing.Size(234, 22)
        Me.GoToXSSShellDownloadPageToolStripMenuItem.Text = "&Go to XSS Shell Download Page"
        '
        'StatusStrip1
        '
        Me.StatusStrip1.Items.AddRange(New System.Windows.Forms.ToolStripItem() {Me.PrgMain, Me.LblStatus})
        Me.StatusStrip1.Location = New System.Drawing.Point(0, 298)
        Me.StatusStrip1.Name = "StatusStrip1"
        Me.StatusStrip1.Size = New System.Drawing.Size(411, 22)
        Me.StatusStrip1.TabIndex = 4
        Me.StatusStrip1.Text = "MainStatus"
        '
        'PrgMain
        '
        Me.PrgMain.Name = "PrgMain"
        Me.PrgMain.Size = New System.Drawing.Size(100, 16)
        Me.PrgMain.Step = 1
        Me.PrgMain.Style = System.Windows.Forms.ProgressBarStyle.Continuous
        '
        'LblStatus
        '
        Me.LblStatus.ForeColor = System.Drawing.SystemColors.ControlDarkDark
        Me.LblStatus.Name = "LblStatus"
        Me.LblStatus.Size = New System.Drawing.Size(50, 17)
        Me.LblStatus.Text = "XSS Shell"
        '
        'MnuEnableCache
        '
        Me.MnuEnableCache.Checked = True
        Me.MnuEnableCache.CheckOnClick = True
        Me.MnuEnableCache.CheckState = System.Windows.Forms.CheckState.Checked
        Me.MnuEnableCache.Name = "MnuEnableCache"
        Me.MnuEnableCache.Size = New System.Drawing.Size(152, 22)
        Me.MnuEnableCache.Text = "&Enable Cache"
        '
        'Main
        '
        Me.AutoScaleDimensions = New System.Drawing.SizeF(6.0!, 13.0!)
        Me.AutoScaleMode = System.Windows.Forms.AutoScaleMode.Font
        Me.ClientSize = New System.Drawing.Size(411, 320)
        Me.Controls.Add(Me.StatusStrip1)
        Me.Controls.Add(Me.TabControl1)
        Me.Controls.Add(Me.ToolStrip1)
        Me.Controls.Add(Me.MenuStrip1)
        Me.Icon = CType(resources.GetObject("$this.Icon"), System.Drawing.Icon)
        Me.MainMenuStrip = Me.MenuStrip1
        Me.Name = "Main"
        Me.Text = "XSS Tunnel"
        Me.ToolStrip1.ResumeLayout(False)
        Me.ToolStrip1.PerformLayout()
        Me.TabControl1.ResumeLayout(False)
        Me.TabPage2.ResumeLayout(False)
        Me.MnuList.ResumeLayout(False)
        Me.GroupBox4.ResumeLayout(False)
        Me.GroupBox4.PerformLayout()
        Me.GroupBox3.ResumeLayout(False)
        Me.GroupBox3.PerformLayout()
        Me.TabPage1.ResumeLayout(False)
        Me.GroupBox5.ResumeLayout(False)
        Me.GroupBox5.PerformLayout()
        CType(Me.TrackTrans, System.ComponentModel.ISupportInitialize).EndInit()
        Me.GroupBox1.ResumeLayout(False)
        Me.GroupBox1.PerformLayout()
        CType(Me.NumPort, System.ComponentModel.ISupportInitialize).EndInit()
        Me.GroupBox2.ResumeLayout(False)
        Me.GroupBox2.PerformLayout()
        Me.TabPage3.ResumeLayout(False)
        Me.TabPage3.PerformLayout()
        Me.MnuLog.ResumeLayout(False)
        Me.MenuStrip1.ResumeLayout(False)
        Me.MenuStrip1.PerformLayout()
        Me.StatusStrip1.ResumeLayout(False)
        Me.StatusStrip1.PerformLayout()
        Me.ResumeLayout(False)
        Me.PerformLayout()

    End Sub
    Friend WithEvents ToolStrip1 As System.Windows.Forms.ToolStrip
    Friend WithEvents BtnStop As System.Windows.Forms.ToolStripButton
    Friend WithEvents TabControl1 As System.Windows.Forms.TabControl
    Friend WithEvents TabPage2 As System.Windows.Forms.TabPage
    Friend WithEvents TabPage1 As System.Windows.Forms.TabPage
    Friend WithEvents GroupBox1 As System.Windows.Forms.GroupBox
    Friend WithEvents NumPort As System.Windows.Forms.NumericUpDown
    Friend WithEvents Label1 As System.Windows.Forms.Label
    Friend WithEvents GroupBox2 As System.Windows.Forms.GroupBox
    Friend WithEvents TxtPassword As System.Windows.Forms.TextBox
    Friend WithEvents Label3 As System.Windows.Forms.Label
    Friend WithEvents TxtServer As System.Windows.Forms.TextBox
    Friend WithEvents Label2 As System.Windows.Forms.Label
    Friend WithEvents BtnTestServer As System.Windows.Forms.Button
    Friend WithEvents Label4 As System.Windows.Forms.Label
    Friend WithEvents CmbInterface As System.Windows.Forms.ComboBox
    Friend WithEvents MenuStrip1 As System.Windows.Forms.MenuStrip
    Friend WithEvents FileToolStripMenuItem As System.Windows.Forms.ToolStripMenuItem
    Friend WithEvents toolStripSeparator2 As System.Windows.Forms.ToolStripSeparator
    Friend WithEvents ExitToolStripMenuItem As System.Windows.Forms.ToolStripMenuItem
    Friend WithEvents CacheToolStripMenuItem As System.Windows.Forms.ToolStripMenuItem
    Friend WithEvents ClearCacheToolStripMenuItem As System.Windows.Forms.ToolStripMenuItem
    Friend WithEvents HelpToolStripMenuItem As System.Windows.Forms.ToolStripMenuItem
    Friend WithEvents AboutToolStripMenuItem As System.Windows.Forms.ToolStripMenuItem
    Friend WithEvents GroupBox3 As System.Windows.Forms.GroupBox
    Friend WithEvents LblVictimID As System.Windows.Forms.Label
    Friend WithEvents Label5 As System.Windows.Forms.Label
    Friend WithEvents GroupBox4 As System.Windows.Forms.GroupBox
    Friend WithEvents Label9 As System.Windows.Forms.Label
    Friend WithEvents Label7 As System.Windows.Forms.Label
    Friend WithEvents Label8 As System.Windows.Forms.Label
    Friend WithEvents LstResponses As System.Windows.Forms.ListView
    Friend WithEvents ToolStripButton2 As System.Windows.Forms.ToolStripMenuItem
    Friend WithEvents BtnStart As System.Windows.Forms.ToolStripButton
    Friend WithEvents PrgVictim As System.Windows.Forms.ProgressBar
    Friend WithEvents TabPage3 As System.Windows.Forms.TabPage
    Friend WithEvents TxtLog As System.Windows.Forms.TextBox
    Friend WithEvents MnuLog As System.Windows.Forms.ContextMenuStrip
    Friend WithEvents ClearLogToolStripMenuItem As System.Windows.Forms.ToolStripMenuItem
    Friend WithEvents LblRequests As System.Windows.Forms.Label
    Friend WithEvents LblCached As System.Windows.Forms.Label
    Friend WithEvents LblResponses As System.Windows.Forms.Label
    Friend WithEvents StatusStrip1 As System.Windows.Forms.StatusStrip
    Friend WithEvents PrgMain As System.Windows.Forms.ToolStripProgressBar
    Friend WithEvents ColumnHeader1 As System.Windows.Forms.ColumnHeader
    Friend WithEvents ColumnHeader2 As System.Windows.Forms.ColumnHeader
    Friend WithEvents ViewToolStripMenuItem As System.Windows.Forms.ToolStripMenuItem
    Friend WithEvents AlwaysOnTopToolStripMenuItem As System.Windows.Forms.ToolStripMenuItem
    Friend WithEvents ToolStripSeparator1 As System.Windows.Forms.ToolStripSeparator
    Friend WithEvents GroupBox5 As System.Windows.Forms.GroupBox
    Friend WithEvents TrackTrans As System.Windows.Forms.TrackBar
    Friend WithEvents ToolStripSeparator3 As System.Windows.Forms.ToolStripSeparator
    Friend WithEvents PortcullisComputerSecurityWebsiteToolStripMenuItem As System.Windows.Forms.ToolStripMenuItem
    Friend WithEvents GoToXSSShellDownloadPageToolStripMenuItem As System.Windows.Forms.ToolStripMenuItem
    Friend WithEvents LblStatus As System.Windows.Forms.ToolStripStatusLabel
    Friend WithEvents MnuList As System.Windows.Forms.ContextMenuStrip
    Friend WithEvents ClearItemsToolStripMenuItem As System.Windows.Forms.ToolStripMenuItem
    Friend WithEvents MnuEnableCache As System.Windows.Forms.ToolStripMenuItem

End Class
