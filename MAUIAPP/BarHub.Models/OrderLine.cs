using BarHub.Models.Enums;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace BarHub.Models
{
    public class OrderLine
    {
        public Guid Id { get; set; }
        public Article Article { get; set; }
        public string ArticleName { get; set; }
        public string ArticlePrice { get; set; }
        public Enums.Enums Estado { get; set; }
    }
}
