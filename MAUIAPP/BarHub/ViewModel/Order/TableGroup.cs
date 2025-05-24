using System;
using System.Collections.Generic;
using System.Collections.ObjectModel;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace BarHub.ViewModel.Order
{
    public class TableGroup : ObservableCollection<SectionGroup>
    {
        public int TableNumber { get; }

        public TableGroup(int tableNumber, IEnumerable<SectionGroup> sections) : base(sections)
        {
            TableNumber = tableNumber;
        }
    }
}
