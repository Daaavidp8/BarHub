using BarHub.Models;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace BarHub.ViewModel.Interfaces
{
    public interface IContext<T>
    {
        event Func<T, Task> ObjectCreated;
        event Func<T, Task> ObjectModified;
        event Func<T, Task> ObjectDeleted;

        Task NotifyObjectCreated(T obj);
        Task NotifyObjectModified(T obj);
        Task NotifyObjectDeleted(T obj);
    }


}
