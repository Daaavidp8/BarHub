using BarHub.Models;
using System;
using System.Collections.Generic;
using System.Collections.ObjectModel;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace BarHub.ViewModel.Order
{
    public class SectionGroup : ObservableCollection<OrderLineViewModel>
    {
        public string SectionName { get; }

        public SectionGroup(string sectionName, IEnumerable<OrderLineViewModel> lines) : base(lines)
        {
            SectionName = sectionName;
        }
    }

}
