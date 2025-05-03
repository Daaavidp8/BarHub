using BarHub.ViewModel.Interfaces;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace BarHub.ViewModel
{
    public class Context<T> : IContext<T>
    {
        public event Func<T, Task> ObjectCreated;
        public event Func<T, Task> ObjectModified;
        public event Func<T, Task> ObjectDeleted;

        public async Task NotifyObjectCreated(T obj)
        {
            if (ObjectCreated != null)
            {
                foreach (var handler in ObjectCreated.GetInvocationList())
                {
                    await ((Func<T, Task>)handler)(obj);
                }
            }
        }

        public async Task NotifyObjectModified(T obj)
        {
            if (ObjectModified != null)
            {
                foreach (var handler in ObjectModified.GetInvocationList())
                {
                    await ((Func<T, Task>)handler)(obj);
                }
            }
        }

        public async Task NotifyObjectDeleted(T obj)
        {
            if (ObjectDeleted != null)
            {
                foreach (var handler in ObjectDeleted.GetInvocationList())
                {
                    await ((Func<T, Task>)handler)(obj);
                }
            }
        }
    }

}
