import java.io.*;
import java.util.*;

public class booking_er {

    public static BufferedReader stdin = new BufferedReader(new InputStreamReader(System.in));
    public static StringTokenizer st;

    public static String TOKEN() throws IOException {
        while(st == null || !st.hasMoreTokens())st = new StringTokenizer(stdin.readLine());
        return st.nextToken();
    }

    public static int INT() throws IOException {
        return Integer.parseInt(TOKEN());
    }

    public static void main(String[] args) throws IOException {

        int cases = INT();

        while (cases-->0) {

            int N = INT(), B = INT();

            Set<String>[] resources = new Set[N];
            for(int i = 0; i < N; i++) {
                resources[i] = new HashSet<String>();
                for(int c = INT(); c > 0; c--)
                    resources[i].add(TOKEN());
            }

            Booking[] bookings = new Booking[B];
            for(int i = 0; i < B; i++) {
                bookings[i] = new Booking(TOKEN(), INT());
                if(bookings[i].type.equals("book")) {
                    for(int d = INT(); d > 0; d--)
                        bookings[i].addDemand(TOKEN());
                }
            }

            new booking_er().solve(resources, bookings);

        }

    }

    private Set<String>[] resources;
    private Map<String, Integer> capabilityNodes;
    private Map<Integer, Integer> idsToBookingNodes;
    private int[] bookingNodesToIds;
    private BookingSystem flowgraph;

    public void solve(Set<String>[] res, Booking[] bookings) {

        idsToBookingNodes = new HashMap<Integer, Integer>();
        this.resources = res;
        bookingNodesToIds = new int[res.length + 1];
        capabilityNodes = new HashMap<String, Integer>();

        Set<String> capabilities = new HashSet<String>();
        for(Set<String> rcap : resources)capabilities.addAll(rcap);

        int nodes = 3 + 2 * resources.length + capabilities.size();

        // Building booking graph
        flowgraph = new BookingSystem(nodes, Math.max(resources.length + 1, 20), 0, 1);


        int at = 3 + 2 * resources.length;
        for(String capability : capabilities)
            capabilityNodes.put(capability, at++);

        for(int i = 0; i < resources.length; i++) {
            flowgraph.addConnection(2 + i, 1, 1);
            for(String rcap : resources[i])
                flowgraph.addConnection(capabilityNodes.get(rcap), 2 + i, 1);
        }


        Arrays.fill(bookingNodesToIds, -1);

        // Running simulation
        for(Booking booking : bookings)
            System.out.println(handleBooking(booking) ? "Accepted" : "Rejected");

    }

    public boolean handleBooking(Booking booking) {
        if(booking.type.equals("book"))
            return addAppointment(booking);
        return cancelAppointment(booking.id);
    }

    public boolean addAppointment(Booking booking) {

        if(idsToBookingNodes.containsKey(booking.id)) {
            return false;
        }

        if(booking.resources.size() == 0) {
            idsToBookingNodes.put(booking.id, -1);

            return true;
        }

        for(String res : booking.resources) {
            if(!capabilityNodes.containsKey(res)) {
                return false;
            }
        }

        int index = getFreeIndex();
        if(index < 0)return false;

        idsToBookingNodes.put(booking.id, index);
        bookingNodesToIds[index] = booking.id;

        flowgraph.addConnection(0, 2 + resources.length + index, booking.resources.size());

        for(String res : booking.resources) {
            flowgraph.addConnection(2 + resources.length + index, capabilityNodes.get(res), 1);
        }

        int increase = flowgraph.increaseFlow();

        if(increase < booking.resources.size()) {
            cancelAppointment(booking.id);
            flowgraph.increaseFlow();
            return false;
        }
        return true;

    }

    private int getFreeIndex() {
        for(int i = 0; i < bookingNodesToIds.length; i++) {
            if(bookingNodesToIds[i] < 0) {
                return i;
            }
        }
        return -1;
    }

    public boolean cancelAppointment(int id) {

        if(!idsToBookingNodes.containsKey(id))
            return false;

        if(idsToBookingNodes.get(id) >= 0) { // In graph!
            int nodeIndex = 2 + resources.length + idsToBookingNodes.get(id);
            flowgraph.clearBooking(nodeIndex);
            bookingNodesToIds[idsToBookingNodes.get(id)] = -1;
        }

        idsToBookingNodes.remove(id);

        return true;
    }

    private static class Booking {
        final String type;
        final int id;
        final List<String> resources;

        Booking(String type, int id) {
            this.type = type;
            this.id = id;
            this.resources = new ArrayList<String>();
        }

        public void addDemand(String demand) {
            this.resources.add(demand);
        }

    }

    private static class BookingSystem {

        private Connection[][] connections;
        private int[] len;
        private int src, snk;

        public BookingSystem(int N, int maxEdge, int source, int sink) {
            this.connections = new Connection[N][maxEdge];
            this.len = new int[N];
            this.src = source;
            this.snk = sink;
        }

        public void addConnection(int start, int end, int capacity) {
            Connection edg = new Connection(start, end, capacity);
            connections[start][len[start]++] = edg;
            connections[end][len[end]++] = edg;
        }

        public void clearBooking(int node) {

            // Removing flow passing through the node
            for(Connection edg : connections[node]) {
                if (edg == null)break;
                if(edg.flow == 0)continue;
                if(edg.start == node) {
                    int at = edg.target;
                    while(at != snk) {
                        for(Connection next : connections[at]) {
                            if(next.start == at && next.flow != 0) {
                                next.flow = 0;
                                at = next.target;
                                break;
                            }
                        }
                    }
                }
            }

            // Removing all connections adjacent to the node
            for(Connection edg : connections[node]) {
                if(edg == null)break;
                int end = edg.getEnd(node);
                for(int i = 0; i < connections[end].length; i++) {
                    if(connections[end][i] == edg) {
                        connections[end][i] = connections[end][--len[end]];
                        connections[end][len[end]] = null;
                    }
                }
            }
            Arrays.fill(connections[node], null);
            len[node] = 0;

        }

        private static class Connection {

            private final int start, target, capacity;
            private int flow;

            public Connection(int start, int target, int capacity) {
                this.start = start;
                this.target = target;
                this.capacity = capacity;
            }

            public int getResidual(int start) {
                if(start==this.start)return capacity-flow;
                return flow;
            }

            public int getEnd(int start) {
                if(start==this.start)return this.target;
                return this.start;
            }

            public void addFlow(int start, int flow) {
                if(start==this.start)this.flow += flow;
                else this.flow -= flow;
            }

        }

        public int increaseFlow() {

            int N = connections.length;
            int flowinc = 0;
            Connection[] parent = new Connection[N];
            boolean[] found = new boolean[N];

            while(true) {
                Arrays.fill(parent, null);
                Arrays.fill(found, false);

                // Finding augmenting path:
                LinkedList<Integer> queue = new LinkedList<Integer>();
                queue.add(src);
                found[src] = true;

out:            while(!queue.isEmpty()) {
                    int at = queue.poll();

                    for(Connection edg : connections[at]) {
                        if(edg == null)break;
                        if(edg.getResidual(at) <= 0)continue;
                        int neigh = edg.getEnd(at);
                        if(!found[neigh]) {
                            parent[neigh] = edg;
                            found[neigh] = true;
                            queue.add(neigh);
                            if(neigh == snk)break out;
                        }


                    }
                }

                if(parent[snk] == null)break; // No augmenting path

                // Calculating and adding new flow:
                int addedFlow = Integer.MAX_VALUE / 3;
                int pos = snk;
                while(parent[pos] != null) {
                    int from = parent[pos].getEnd(pos);
                    addedFlow = Math.min(addedFlow, parent[pos].getResidual(from));
                    pos = from;
                }

                flowinc += addedFlow;
                pos = snk;
                while(parent[pos] != null) {
                    int from = parent[pos].getEnd(pos);
                    parent[pos].addFlow(from, addedFlow);
                    pos = from;
                }
            }

            return flowinc;

        }
    }
}