import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStreamReader;
import java.util.Arrays;
import java.util.StringTokenizer;

public class space_er {

    public static final double EPSILON = 1e-9;

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

            int N = INT();

            PointVector3D start = new PointVector3D(INT(), INT(), INT());
            PointVector3D end = new PointVector3D(INT(), INT(), INT());

            Segment3D[] segments = new Segment3D[N];
            for(int i = 0; i < N; i++) {
                segments[i] = new Segment3D(
                        new PointVector3D(INT(),INT(),INT()),
                        new PointVector3D(INT(),INT(),INT())
                );
            }

            System.out.println(new space_er().solve(start, end, segments));

        }

    }


    private double solve(PointVector3D start, PointVector3D end, Segment3D[] segments) {

        int N = 2 + segments.length;

        double[][] distances = new double[N][N];
        distances[0][1] = distances[1][0] = start.distanceTo(end);
        for(int i = 0; i < segments.length; i++) {
            distances[0][2+i] = distances[2+i][0] = segments[i].pointDistance(start);
            distances[1][2+i] = distances[2+i][1] = segments[i].pointDistance(end);
            for(int j = 0; j < segments.length; j++) {
                if(j == i) continue;
                distances[2+i][2+j] = distances[2+j][2+i] = segments[i].segmentDistance(segments[j]);
            }
        }

        return dijkstra(distances);
    }

    private double dijkstra(double[][] nm) {

        int N = nm.length;
        boolean[] done = new boolean[N];
        double[] dist = new double[N];

        Arrays.fill(dist, Double.MAX_VALUE / 3);
        dist[0] = 0;

        int next = 0;
        for(int i = 0; i < N; i++) {
            done[next] = true;
            int node = -1;
            double best = Double.MAX_VALUE / 2;
            for (int j = 0; j < N; j++) {
                dist[j] = Math.min(dist[j], dist[next] + nm[next][j]);
                if(!done[j] && dist[j] < best) {
                    node = j;
                    best = dist[j];
                }
            }
            next = node;
        }

        return dist[1];
    }


    private static class Segment3D {
        private final PointVector3D a, b;

        public Segment3D(PointVector3D a, PointVector3D b) {
            this.a = a;
            this.b = b;
        }

        public double segmentDistance(Segment3D segment) {

            PointVector3D delta = segment.b.subtract(segment.a);
            double min = 0, max = 1.0;

            int iterations = 100;
            while(iterations-- > 0) {
                double left = min + (max - min) / 3.0;
                double right = min + 2 * (max-min) / 3.0;

                double ldist = pointDistance(
                        new PointVector3D(
                            segment.a.x + left * delta.x,
                            segment.a.y + left * delta.y,
                            segment.a.z + left * delta.z
                        )
                );

                double rdist = pointDistance(
                        new PointVector3D(
                                segment.a.x + right * delta.x,
                                segment.a.y + right * delta.y,
                                segment.a.z + right * delta.z
                        )
                );

                if(ldist < rdist) {
                    max = right;
                } else {
                    min = left;
                }

            }

            return pointDistance(
                    new PointVector3D(
                            segment.a.x + min * delta.x,
                            segment.a.y + min * delta.y,
                            segment.a.z + min * delta.z
                    )
            );

        }

        public double pointDistance(PointVector3D p) {

            if(a.equals(p) || b.equals(p))return 0.0;

            if(isSinglePoint())return a.distanceTo(p);

            if(this.angleTo(p) > Math.PI / 2 || new Segment3D(b,a).angleTo(p) > Math.PI / 2)
                return Math.min(p.distanceTo(a), p.distanceTo(b));

            PointVector3D v1 = b.subtract(a), v2 = p.subtract(a);

            PointVector3D cross = new PointVector3D(
                    v1.y*v2.z - v2.y*v1.z,
                    v1.z*v2.x - v2.z*v1.x,
                    v1.x*v2.y - v2.x*v1.y
            );

            return cross.originDistance() / v1.originDistance();

        }

        public double angleTo(PointVector3D p) {
            PointVector3D v1 = a.subtract(b);
            PointVector3D v2 = p.subtract(b);

            double val = v1.dot(v2) /
                    (v1.originDistance() * v2.originDistance());

            // handling rounding issues from the previous step
            if(val < -1.0)val = -1.0;
            if(val > 1.0)val = 1.0;

            return Math.acos(val);

        }

        public boolean isSinglePoint() {
            return a.equals(b);
        }

    }

    private static class PointVector3D {
        private final double x, y, z;

        public PointVector3D(double x, double y, double z) {
            this.x = x;
            this.y = y;
            this.z = z;
        }

        public double distanceTo(PointVector3D p) {
            PointVector3D delta = new PointVector3D(p.x - this.x, p.y - this.y, p.z - this.z);
            return delta.originDistance();
        }

        public PointVector3D subtract(PointVector3D p) {
            return new PointVector3D(x - p.x, y - p.y, z - p.z);
        }

        public double originDistance() {
            return Math.sqrt(x*x + y*y + z*z);
        }

        public double dot(PointVector3D p) {
            return x*p.x + y*p.y + z*p.z;
        }

        public boolean equals(Object obj) {
            if(obj instanceof PointVector3D) {
                PointVector3D p = (PointVector3D)obj;

                if(Math.abs(this.x - p.x) < EPSILON && Math.abs(this.y-p.y) < EPSILON && Math.abs(this.z - p.z) < EPSILON)
                    return true;

            }
            return false;
        }

        public String toString() {
            return "[" + this.x + ", " + this.y + ", " + this.z + "]";
        }
    }
}
