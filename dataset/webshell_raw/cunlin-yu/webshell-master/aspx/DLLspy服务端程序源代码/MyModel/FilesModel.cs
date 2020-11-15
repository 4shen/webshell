using System;
using System.Collections.Generic;
using System.Text;

namespace MyModel
{
    public class FilesModel
    {
        private int id;
        /// <summary>
        /// ID
        /// </summary>
        public int Id
        {
            get { return id; }
            set { id = value; }
        }
        /// <summary>
        /// 类型
        /// </summary>
        private int type;

        public int Type
        {
            get { return type; }
            set { type = value; }
        }
        private string fileName;
        /// <summary>
        /// 文件名
        /// </summary>
        public string FileName
        {
            get { return fileName; }
            set { fileName = value; }
        }
        private string fullPath;
        /// <summary>
        /// 路径
        /// </summary>
        public string FullPath
        {
            get { return fullPath; }
            set { fullPath = value; }
        }
        private long fileSize;

        /// <summary>
        /// 大小
        /// </summary>
        public long FileSize
        {
            get { return fileSize; }
            set { fileSize = value; }
        }
        private string lastWrite;
        
        /// <summary>
        /// 最后写入
        /// </summary>
        public string LastWrite
        {
            get { return lastWrite; }
            set { lastWrite = value; }
        }
        private string creatTime;

        /// <summary>
        /// 创建时间
        /// </summary>
        public string CreatTime
        {
            get { return creatTime; }
            set { creatTime = value; }
        }

    
    }
}
